<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\DI;

use Tracy\Bar;
use Tester\Assert;
use Tester\TestCase;
use Nette\DI\InvalidConfigurationException;
use SixtyEightPublishers\i18n\Diagnostics\Panel;
use SixtyEightPublishers\i18n\ProfileProviderInterface;
use SixtyEightPublishers\i18n\Detector\DetectorInterface;
use SixtyEightPublishers\i18n\Tests\Fixture\DummyDetector;
use SixtyEightPublishers\i18n\Tests\Helper\ContainerFactory;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\Tests\Fixture\DummyProfileStorage;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;
use SixtyEightPublishers\i18n\Translation\TranslatorLocaleResolver;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;
use SixtyEightPublishers\TranslationBridge\Localization\TranslatorLocalizerInterface;

require __DIR__ . '/../../bootstrap.php';

final class I18nExtensionIntegrationTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testBaseConfiguration(): void
	{
		$container = ContainerFactory::createContainer(CONFIG_DIR . '/i18n.neon');

		# Autowired dependencies
		Assert::noError(static function () use ($container) {
			$container->getByType(ProfileProviderInterface::class);
		});

		Assert::noError(static function () use ($container) {
			$container->getByType(ActiveProfileChangeNotifier::class);
		});

		# Non autowired dependencies
		Assert::type(ProfileContainerInterface::class, $container->getService('68publishers.i18n.profile_container'));
		Assert::type(ProfileStorageInterface::class, $container->getService('68publishers.i18n.storage'));
		Assert::type(DetectorInterface::class, $container->getService('68publishers.i18n.detector'));

		# Tracy is disabled
		/** @var \Tracy\Bar $bar */
		$bar = $container->getByType(Bar::class);

		Assert::equal(NULL, $bar->getPanel(Panel::class));

		# check if defined properties are successfully passed
		/** @var \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface $profiles */
		$profiles = $container->getService('68publishers.i18n.profile_container');

		Assert::noError(static function () use ($profiles) {
			$profiles->get();
			$profiles->get('default');
			$profiles->get('foo');
			$profiles->get('bar');
		});

		Assert::same($profiles->get(), $profiles->get('default'));

		$default = $profiles->get();
		$foo = $profiles->get('foo');
		$bar = $profiles->get('bar');

		Assert::equal('default', $default->getName());
		Assert::equal([ 'cs_CZ', 'sk_SK' ], $default->getLanguages());
		Assert::equal([ 'CZ', 'SK' ], $default->getCountries());
		Assert::equal([ 'CZK', 'EUR' ], $default->getCurrencies());
		Assert::equal([], $default->getDomains());
		Assert::equal(TRUE, $default->isEnabled());

		Assert::equal('foo', $foo->getName());
		Assert::equal([ 'en_US' ], $foo->getLanguages());
		Assert::equal([ 'GB', 'USA' ], $foo->getCountries());
		Assert::equal([ 'EUR', 'USD', 'GBP' ], $foo->getCurrencies());
		Assert::equal([ 'example\.com\/foo\/' ], $foo->getDomains());
		Assert::equal(TRUE, $foo->isEnabled());

		Assert::equal('bar', $bar->getName());
		Assert::equal([ 'de_DE' ], $bar->getLanguages());
		Assert::equal([ 'DE' ], $bar->getCountries());
		Assert::equal([ 'EUR' ], $bar->getCurrencies());
		Assert::equal([ 'example\.com\/bar\/' ], $bar->getDomains());
		Assert::equal(FALSE, $bar->isEnabled());
	}

	/**
	 * @return void
	 */
	public function testProfileConfigurationWithoutDefaultProfileDefinition(): void
	{
		$container = ContainerFactory::createContainer(CONFIG_DIR . '/i18n_without_default_profile_definition.neon');

		/** @var \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface $profiles */
		$profiles = $container->getService('68publishers.i18n.profile_container');

		# first profile is matched as default
		Assert::same($profiles->get(), $profiles->get('foo'));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingProfilesConfiguration(): void
	{
		Assert::exception(
			static function () {
				ContainerFactory::createContainer(CONFIG_DIR . '/i18n_without_profiles.neon');
			},
			InvalidConfigurationException::class,
			'#(.*)' . preg_quote('You must define almost one profile in your configuration.', '#') . '(.*)#i'
		);
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingRequiredProfileSetting(): void
	{
		Assert::exception(
			static function () {
				ContainerFactory::createContainer(CONFIG_DIR . '/i18n_without_required_profile_setting.neon');
			},
			InvalidConfigurationException::class,
			'#.*' . preg_quote('Almost one language must be defined.', '#') . '.*#i'
		);
	}

	/**
	 * @return void
	 */
	public function testCustomStorageAndDetector(): void
	{
		$container = ContainerFactory::createContainer(CONFIG_DIR . '/i18n_with_custom_storage_and_detector.neon');

		Assert::type(DummyProfileStorage::class, $container->getService('68publishers.i18n.storage'));
		Assert::type(DummyDetector::class, $container->getService('68publishers.i18n.detector'));
	}

	/**
	 * @return void
	 */
	public function testDebuggerEnabled(): void
	{
		$container = ContainerFactory::createContainer(CONFIG_DIR . '/i18n_with_debugger_enabled.neon');

		/** @var \Tracy\Bar $bar */
		$bar = $container->getByType(Bar::class);

		Assert::type(Panel::class, $bar->getPanel(Panel::class));
	}

	/**
	 * @return void
	 */
	public function testTranslationBridgeFeatures(): void
	{
		$container = ContainerFactory::createContainer(CONFIG_DIR . '/i18n_with_translation_bridge.neon');

		# Is resolver registered?
		Assert::type(TranslatorLocaleResolver::class, $container->getService('68publishers.i18n.translator_locale_resolver'));

		# Test synchronization between Profiles and Translator
		$profileProvider = $container->getByType(ProfileProviderInterface::class);
		$localizer = $container->getByType(TranslatorLocalizerInterface::class);

		Assert::same('cs', $localizer->getLocale());

		$profileProvider->getProfile()->changeLanguage('sk_SK');

		Assert::same('sk_SK', $localizer->getLocale());
	}
}

(new I18nExtensionIntegrationTest())->run();
