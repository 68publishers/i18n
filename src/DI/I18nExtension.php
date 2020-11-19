<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\DI;

use Nette\DI\Helpers;
use Nette\DI\Statement;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\PhpLiteral;
use SixtyEightPublishers\i18n\Profile\Profile;
use SixtyEightPublishers\i18n\ProfileProvider;
use SixtyEightPublishers\i18n\Diagnostics\Panel;
use SixtyEightPublishers\i18n\Lists\ListOptions;
use SixtyEightPublishers\i18n\Lists\LanguageList;
use SixtyEightPublishers\i18n\ProfileProviderInterface;
use SixtyEightPublishers\i18n\Detector\DetectorInterface;
use SixtyEightPublishers\i18n\Detector\NetteRequestDetector;
use SixtyEightPublishers\i18n\Storage\SessionProfileStorage;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\Exception\ConfigurationException;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainer;
use SixtyEightPublishers\i18n\Translation\ProfileStorageResolver;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

final class I18nExtension extends CompilerExtension
{
	/** @var array  */
	private $defaults = [
		'profiles' => [],
		'debugger' => '%debugMode%',
		'translations' => [
			'enabled' => FALSE,
			'use_default' => FALSE,
		],
		'lists' => [
			'vendorDir' => '%appDir%/../vendor',
			'fallback_language' => 'en',
			'default_language' => NULL,
		],
		'storage' => SessionProfileStorage::class,
		'detector' => NetteRequestDetector::class,
	];

	/** @var array  */
	private $profileDefaults = [
		'language' => [],
		'currency' => [],
		'country' => [],
		'domain' =>  [],
		'enabled' => TRUE,
	];

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);
		$profiles = $config['profiles'];

		# validations
		Validators::assertField($config, 'profiles', 'array');
		Validators::assertField($config, 'debugger', 'bool');
		Validators::assertField($config['translations'], 'enabled', 'bool');
		Validators::assertField($config['translations'], 'use_default', 'bool');
		Validators::assertField($config, 'storage', 'string|' . Statement::class);
		Validators::assertField($config, 'detector', 'string|' . Statement::class);

		Validators::assertField($config, 'lists', 'array');
		Validators::assertField($config['lists'], 'vendorDir', 'string');
		Validators::assertField($config['lists'], 'fallback_language', 'string');
		Validators::assertField($config['lists'], 'default_language', 'null|string');

		if (empty($profiles)) {
			throw new ConfigurationException('You must define almost one profile in your configuration.');
		}

		# ActiveProfile Change Notifier
		$builder->addDefinition($this->prefix('active_profile_change_notifier'))
			->setType(ActiveProfileChangeNotifier::class);

		# Register profile's storage
		if (TRUE === $this->needRegister($config['storage'])) {
			$config['storage'] = $builder->addDefinition($this->prefix('storage'))
				->setType(ProfileStorageInterface::class)
				->setFactory($config['storage'])
				->setAutowired(FALSE);
		}

		# Register profile's detector
		if (TRUE === $this->needRegister($config['detector'])) {
			$config['detector'] = $builder->addDefinition($this->prefix('detector'))
				->setType(DetectorInterface::class)
				->setFactory($config['detector'])
				->setAutowired(FALSE);
		}

		# create default Profile
		if (isset($profiles['default'])) {
			$defaultProfile = $this->createProfile('default', (array) $profiles['default']);
			unset($profiles['default']);
		} else {
			$defaultProfile = $this->createProfile(key($profiles), (array) array_shift($profiles));
		}

		# register container
		$profileContainer = $builder->addDefinition($this->prefix('profile_container'))
			->setType(ProfileContainerInterface::class)
			->setFactory(ProfileContainer::class, [
				'defaultProfile' => $defaultProfile,
				'profiles' => array_map(function ($config, $key) {
					return $this->createProfile((string) $key, (array) $config);
				}, $profiles, array_keys($profiles)),
			])
			->setAutowired(FALSE);

		# register profile provider
		$builder->addDefinition($this->prefix('profile_provider'))
			->setType(ProfileProviderInterface::class)
			->setFactory(ProfileProvider::class, [
				$config['detector'],
				$config['storage'],
				$profileContainer,
			]);

		# register lists

		$listOptions = $builder->addDefinition($this->prefix('list_options'))
			->setType(ListOptions::class)
			->setArguments([
				'vendorDir' => realpath($config['lists']['vendorDir']),
				'fallbackLanguage' => $config['lists']['fallback_language'],
				'defaultLanguage' => $config['lists']['default_language'],
			])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('list.language'))
			->setType(LanguageList::class)
			->setArguments([
				'options' => $listOptions,
			]);

		# register kdyby/translation integration
		if (TRUE === $config['translations']['enabled']) {
			$this->registerTranslations((bool) $config['translations']['use_default']);
		}

		# register tracy panel
		if (TRUE === $config['debugger'] && interface_exists('Tracy\IBarPanel') && class_exists('Tracy\Bar')) {
			$builder->addDefinition($this->prefix('tracy_panel'))
				->setType(Panel::class)
				->setArguments([
					'profileContainer' => $profileContainer,
				])
				->setAutowired(FALSE);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		if (TRUE === $builder->hasDefinition($this->prefix('tracy_panel'))) {
			$builder->getDefinitionByType('Tracy\Bar')
				->addSetup('addPanel', [
					'panel' => $this->prefix('@tracy_panel'),
				]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateConfig(array $expected, array $config = NULL, $name = NULL): array
	{
		$args = func_get_args();

		/** @noinspection PhpInternalEntityUsedInspection */
		$args[0] = Helpers::expand($expected, $this->getContainerBuilder()->parameters);

		return parent::validateConfig(...$args);
	}

	/**
	 * @param mixed $definition
	 *
	 * @return bool
	 */
	private function needRegister($definition): bool
	{
		return (!is_string($definition) || !Strings::startsWith($definition, '@'));
	}

	/**
	 * @param bool $useDefault
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\i18n\Exception\ConfigurationException
	 */
	protected function registerTranslations(bool $useDefault): void
	{
		$builder = $this->getContainerBuilder();
		$extensions = $this->compiler->getExtensions($extensionClass = 'Kdyby\Translation\DI\TranslationExtension');

		if (empty($extensions)) {
			throw new ConfigurationException(sprintf(
				'You should register %s before %s.',
				$extensionClass,
				get_class($this)
			), E_USER_NOTICE);
		}

		/** @var \Nette\DI\CompilerExtension $extension */
		$extension = $extensions[array_keys($extensions)[0]];

		$builder->addDefinition($this->prefix('translation_resolver'))
			->setType(ProfileStorageResolver::class)
			->setArguments([
				'useDefault' => $useDefault,
			]);

		$chain = $builder->getDefinition($extension->name . '.userLocaleResolver');
		$chain->addSetup('addResolver', [
			$this->prefix('@translation_resolver'),
		]);

		$builder->getDefinition($this->prefix('active_profile_change_notifier'))
			->addSetup('addOnLanguageChangeListener', [
				'listener' => new PhpLiteral('function ($profile) { $this->getByType(\'Kdyby\\Translation\\Translator\')->setLocale($profile->language); }'),
			]);

		# @todo: Add resolver to Tracy Bar
	}

	/**
	 * @param string $name
	 * @param array  $config
	 *
	 * @return \Nette\DI\Statement
	 * @throws \Nette\Utils\AssertionException
	 * @throws \SixtyEightPublishers\i18n\Exception\ConfigurationException
	 */
	private function createProfile(string $name, array $config): Statement
	{
		$config = $this->validateConfig($this->profileDefaults, $config);

		Validators::assertField($config, 'language', 'string|array');
		Validators::assertField($config, 'country', 'string|array');
		Validators::assertField($config, 'currency', 'string|array');
		Validators::assertField($config, 'domain', 'string|array');
		Validators::assertField($config, 'enabled', 'bool');

		$language = is_array($config['language']) ? $config['language'] : [ $config['language'] ];
		$country = is_array($config['country']) ? $config['country'] : [ $config['country'] ];
		$currency = is_array($config['currency']) ? $config['currency'] : [ $config['currency'] ];
		$domain = is_array($config['domain']) ? $config['domain'] : [ $config['domain'] ];

		foreach ([ 'language' => $language, 'country' => $country, 'currency' => $currency ] as $k => $v) {
			if (!empty($v)) {
				continue;
			}

			throw new ConfigurationException(sprintf(
				'Please define almost one %s for configuration key %s.profiles.%s.%s',
				$k,
				$this->name,
				$name,
				$k
			));
		}

		return new Statement(Profile::class, [
			'name' => $name,
			'languages' => $language,
			'countries' => $country,
			'currencies' => $currency,
			'domains' => $domain,
			'enabled' => $config['enabled'],
		]);
	}
}
