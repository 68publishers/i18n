<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\DI;

use Tracy\Bar;
use ReflectionClass;
use Tracy\IBarPanel;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ReflectionException;
use Nette\DI\CompilerExtension;
use Composer\Autoload\ClassLoader;
use Nette\PhpGenerator\PhpLiteral;
use Nette\DI\Definitions\Statement;
use SixtyEightPublishers\i18n\Profile\Profile;
use SixtyEightPublishers\i18n\ProfileProvider;
use SixtyEightPublishers\i18n\Diagnostics\Panel;
use SixtyEightPublishers\i18n\Lists\ListOptions;
use SixtyEightPublishers\i18n\Lists\LanguageList;
use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\ProfileProviderInterface;
use SixtyEightPublishers\i18n\Detector\DetectorInterface;
use SixtyEightPublishers\i18n\Exception\RuntimeException;
use SixtyEightPublishers\i18n\Detector\NetteRequestDetector;
use SixtyEightPublishers\i18n\Storage\SessionProfileStorage;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainer;
use SixtyEightPublishers\i18n\Exception\InvalidArgumentException;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;
use SixtyEightPublishers\i18n\Translation\TranslatorLocaleResolver;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;
use SixtyEightPublishers\TranslationBridge\DI\AbstractTranslationBridgeExtension;
use SixtyEightPublishers\TranslationBridge\Localization\TranslatorLocalizerInterface;
use SixtyEightPublishers\TranslationBridge\Localization\TranslatorLocaleResolverInterface;

final class I18nExtension extends CompilerExtension
{
	public const DEFAULT_PROFILE_NAME = 'default';

	/** @var bool  */
	private $debugMode;

	/** @var string  */
	private $vendorDir;

	/**
	 * @param bool        $debugMode
	 * @param string|NULL $vendorDir
	 */
	public function __construct(bool $debugMode = FALSE, ?string $vendorDir = NULL)
	{
		if (0 >= func_num_args()) {
			throw new InvalidArgumentException(sprintf('Provide Debug mode, e.q. %s(%%consoleMode%%).', static::class));
		}

		$this->debugMode = $debugMode;
		$this->vendorDir = $this->resolveVendorDir($vendorDir);
	}

	/**
	 * @return \Nette\Schema\Schema
	 */
	public function getConfigSchema(): Schema
	{
		$profileAttributeExpectationFactory = static function (string $attribute) {
			return Expect::anyOf(Expect::string(), Expect::arrayOf('string'))
				->required()
				->castTo('array')
				->assert(static function (array $value) {
					return !empty($value);
				}, sprintf('Almost one %s must be defined.', $attribute));
		};

		$schema = Expect::structure([
			'profiles' => Expect::arrayOf(Expect::structure([
				'language' => $profileAttributeExpectationFactory('language'),
				'currency' => $profileAttributeExpectationFactory('currency'),
				'country' => $profileAttributeExpectationFactory('country'),
				'domain' => Expect::anyOf(Expect::string(), Expect::arrayOf('string'))->default([])->castTo('array'),
				'enabled' => Expect::bool(TRUE),
			])),

			'lists' => Expect::structure([
				'fallback_language' => Expect::string('en'),
				'default_language' => Expect::string()->nullable(),
			]),

			'storage' => Expect::anyOf(Expect::string(), Expect::type(Statement::class))
				->default(SessionProfileStorage::class)
				->before(static function ($def) {
					return $def instanceof Statement ? $def : new Statement($def);
				}),

			'detector' => Expect::anyOf(Expect::string(), Expect::type(Statement::class))
				->default(NetteRequestDetector::class)
				->before(static function ($def) {
					return $def instanceof Statement ? $def : new Statement($def);
				}),

			'translation_bridge' => Expect::structure([
				'locale_resolver' => Expect::structure([
					'enabled' => Expect::bool(FALSE),
					'use_default' => Expect::bool(FALSE),
					'priority' => Expect::int(15),
				]),
			]),
		]);

		$schema->assert(static function ($schema) {
			return !empty($schema->profiles);
		}, 'You must define almost one profile in your configuration.');

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		# ActiveProfile Change Notifier
		$builder->addDefinition($this->prefix('active_profile_change_notifier'))
			->setType(ActiveProfileChangeNotifier::class);

		# Register profile's storage
		$builder->addDefinition($this->prefix('storage'))
			->setType(ProfileStorageInterface::class)
			->setFactory($this->config->storage)
			->setAutowired(FALSE);

		# Register profile's detector
		$builder->addDefinition($this->prefix('detector'))
			->setType(DetectorInterface::class)
			->setFactory($this->config->detector)
			->setAutowired(FALSE);

		# Create default Profile
		$profiles = $this->config->profiles;

		if (isset($profiles[self::DEFAULT_PROFILE_NAME])) {
			$defaultProfile = $this->createProfile(self::DEFAULT_PROFILE_NAME, $profiles[self::DEFAULT_PROFILE_NAME]);
			unset($profiles[self::DEFAULT_PROFILE_NAME]);
		} else {
			$defaultProfile = $this->createProfile((string) key($profiles), array_shift($profiles));
		}

		# Register container
		$builder->addDefinition($this->prefix('profile_container'))
			->setType(ProfileContainerInterface::class)
			->setFactory(ProfileContainer::class, [
				'defaultProfile' => $defaultProfile,
				'profiles' => array_map(function ($config, $key) {
					return $this->createProfile((string) $key, $config);
				}, $profiles, array_keys($profiles)),
			])
			->setAutowired(FALSE);

		# Register profile provider
		$builder->addDefinition($this->prefix('profile_provider'))
			->setType(ProfileProviderInterface::class)
			->setFactory(ProfileProvider::class, [
				$this->prefix('@detector'),
				$this->prefix('@storage'),
				$this->prefix('@profile_container'),
			]);

		# Register lists
		$builder->addDefinition($this->prefix('list_options'))
			->setType(ListOptions::class)
			->setArguments([
				'vendorDir' => $this->vendorDir,
				'fallbackLanguage' => $this->config->lists->fallback_language,
				'defaultLanguage' => $this->config->lists->default_language,
			])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('list.language'))
			->setType(LanguageList::class)
			->setArguments([
				'options' => $this->prefix('@list_options'),
			]);

		# Register tracy panel
		if ($this->debugMode && interface_exists(IBarPanel::class) && class_exists(Bar::class)) {
			$builder->addDefinition($this->prefix('tracy_panel'))
				->setType(Panel::class)
				->setArguments([
					'profileContainer' => $this->prefix('@profile_container'),
				])
				->setAutowired(FALSE);
		}

		$this->registerTranslationBridgeFeatures();
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		if (TRUE === $builder->hasDefinition($this->prefix('tracy_panel'))) {
			$builder->getDefinitionByType(Bar::class)
				->addSetup('addPanel', [$this->prefix('@tracy_panel')]);
		}
	}

	/**
	 * @return void
	 */
	private function registerTranslationBridgeFeatures(): void
	{
		if (empty($this->compiler->getExtensions(AbstractTranslationBridgeExtension::class))) {
			return;
		}

		$builder = $this->getContainerBuilder();

		# Automatically change the Translator's locale when the ActiveProfile's locale is changed
		$builder->getDefinition($this->prefix('active_profile_change_notifier'))
			->addSetup('$service->addOnLanguageChangeListener(function (? $profile) { $this->getByType(?::class)->setLocale($profile->language); })', [
				new PhpLiteral(ActiveProfile::class),
				new PhpLiteral(TranslatorLocalizerInterface::class),
			]);

		$localeResolverConfig = $this->config->translation_bridge->locale_resolver;

		if (!$localeResolverConfig->enabled) {
			return;
		}

		# Add Translator Locale Resolver
		$builder->addDefinition($this->prefix('translator_locale_resolver'))
			->setType(TranslatorLocaleResolverInterface::class)
			->setFactory(TranslatorLocaleResolver::class, [
				'useDefault' => $localeResolverConfig->use_default,
			])
			->setAutowired(FALSE)
			->addTag(AbstractTranslationBridgeExtension::TAG_TRANSLATOR_LOCALE_RESOLVER, $localeResolverConfig->priority);
	}

	/**
	 * @param string $name
	 * @param object $config
	 *
	 * @return \Nette\DI\Definitions\Statement
	 */
	private function createProfile(string $name, object $config): Statement
	{
		return new Statement(Profile::class, [
			'name' => $name,
			'languages' => $config->language,
			'countries' => $config->country,
			'currencies' => $config->currency,
			'domains' => $config->domain,
			'enabled' => $config->enabled,
		]);
	}

	/**
	 * @param string|NULL $vendorDir
	 *
	 * @return string
	 */
	private function resolveVendorDir(?string $vendorDir = NULL): string
	{
		if (NULL !== $vendorDir) {
			return $vendorDir;
		}

		if (!class_exists(ClassLoader::class)) {
			throw new RuntimeException(sprintf(
				'Vendor directory can\'t be detected because the class %s can\'t be found. Please provide the vendor directory manually.',
				ClassLoader::class
			));
		}

		try {
			$reflection = new ReflectionClass(ClassLoader::class);

			return dirname($reflection->getFileName(), 2);
		} catch (ReflectionException $e) {
			throw new RuntimeException('Vendor directory can\'t be detected. Please provide the vendor directory manually.', 0, $e);
		}
	}
}
