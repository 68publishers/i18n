<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\DI;

use Tracy\Bar;
use Tester\Assert;
use Tester\TestCase;
use Nette\DI\Container;
use SixtyEightPublishers\i18n\Diagnostics\Panel;
use SixtyEightPublishers\i18n\ProfileProviderInterface;
use SixtyEightPublishers\i18n\Detector\DetectorInterface;
use SixtyEightPublishers\i18n\Tests\Fixture\DummyDetector;
use SixtyEightPublishers\i18n\Tests\Helper\ContainerFactory;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\Exception\ConfigurationException;
use SixtyEightPublishers\i18n\Tests\Fixture\DummyProfileStorage;
use SixtyEightPublishers\i18n\Translation\ProfileStorageResolver;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

require __DIR__ . '/../../bootstrap.php';

final class I18nExtensionIntegrationTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testBaseConfiguration(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n.neon');

		# autowired dependencies
		Assert::noError(function () use ($container) {
			$container->getByType(ProfileProviderInterface::class);
		});

		Assert::noError(function () use ($container) {
			$container->getByType(ActiveProfileChangeNotifier::class);
		});

		# no-autowired dependencies
		Assert::type(ProfileContainerInterface::class, $container->getService('i18n.profile_container'));
		Assert::type(ProfileStorageInterface::class, $container->getService('i18n.storage'));
		Assert::type(DetectorInterface::class, $container->getService('i18n.detector'));

		# tracy is disabled
		/** @var \Tracy\Bar $bar */
		$bar = $container->getByType(Bar::class);

		Assert::equal(NULL, $bar->getPanel(Panel::class));

		# check if defined properties are successfully passed
		/** @var \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface $profiles */
		$profiles = $container->getService('i18n.profile_container');

		Assert::noError(function () use ($profiles) {
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
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_without_default_profile_definition.neon');

		/** @var \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface $profiles */
		$profiles = $container->getService('i18n.profile_container');

		# first profile is matched as default
		Assert::same($profiles->get(), $profiles->get('foo'));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingProfilesConfiguration(): void
	{
		Assert::exception(
			function () {
				$this->createContainer(__METHOD__);
			},
			ConfigurationException::class,
			'You must define almost one profile in your configuration.'
		);
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingRequiredProfileSetting(): void
	{
		Assert::exception(
			function () {
				$this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_without_required_profile_setting.neon');
			},
			ConfigurationException::class,
			'Please define almost one language for configuration key i18n.profiles.default.language'
		);
	}

	/**
	 * @return void
	 */
	public function testCustomStorageAndDetector(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_with_custom_storage_and_detector.neon');

		Assert::type(DummyProfileStorage::class, $container->getService('i18n.storage'));
		Assert::type(DummyDetector::class, $container->getService('i18n.detector'));
	}

	/**
	 * @return void
	 */
	public function testDebuggerEnabled(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_with_debugger_enabled.neon');

		/** @var \Tracy\Bar $bar */
		$bar = $container->getByType(Bar::class);

		Assert::type(
			Panel::class,
			$bar->getPanel(Panel::class)
		);
	}

	/**
	 * @return void
	 */
	public function testTranslationsEnabled(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_with_translations_enabled.neon');

		Assert::type(ProfileStorageResolver::class, $container->getService('i18n.translation_resolver'));
		# test chain resolver and dispatched event?
	}

	/**
	 * @param string            $method
	 * @param array|string|NULL $config
	 *
	 * @return \Nette\DI\Container
	 */
	private function createContainer(string $method, $config = NULL): Container
	{
		return ContainerFactory::createContainer(static::class . '.' . $method, $config);
	}
}

(new I18nExtensionIntegrationTest())->run();
