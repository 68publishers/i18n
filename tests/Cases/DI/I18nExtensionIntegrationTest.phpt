<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\DI;

use Nette;
use Tracy;
use Tester;
use SixtyEightPublishers;

require __DIR__ . '/../../bootstrap.php';

final class I18nExtensionIntegrationTest extends Tester\TestCase
{
	/**
	 * @return void
	 */
	public function testBaseConfiguration(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n.neon');

		# autowired dependencies
		Tester\Assert::noError(function () use ($container) {
			$container->getByType(SixtyEightPublishers\i18n\IProfileProvider::class);
		});

		Tester\Assert::noError(function () use ($container) {
			$container->getByType(SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier::class);
		});

		# no-autowired dependencies
		Tester\Assert::type(SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer::class, $container->getService('i18n.profile_container'));
		Tester\Assert::type(SixtyEightPublishers\i18n\Storage\IProfileStorage::class, $container->getService('i18n.storage'));
		Tester\Assert::type(SixtyEightPublishers\i18n\Detector\IDetector::class, $container->getService('i18n.detector'));

		# tracy is disabled
		/** @var \Tracy\Bar $bar */
		$bar = $container->getByType(Tracy\Bar::class);

		Tester\Assert::equal(NULL, $bar->getPanel(SixtyEightPublishers\i18n\Diagnostics\Panel::class));

		# check if defined properties are successfully passed
		/** @var \SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer $profiles */
		$profiles = $container->getService('i18n.profile_container');

		Tester\Assert::noError(function () use ($profiles) {
			$profiles->get();
			$profiles->get('default');
			$profiles->get('foo');
			$profiles->get('bar');
		});

		Tester\Assert::same($profiles->get(), $profiles->get('default'));

		$default = $profiles->get();
		$foo = $profiles->get('foo');
		$bar = $profiles->get('bar');

		Tester\Assert::equal('default', $default->getName());
		Tester\Assert::equal([ 'cs_CZ', 'sk_SK' ], $default->getLanguages());
		Tester\Assert::equal([ 'CZ', 'SK' ], $default->getCountries());
		Tester\Assert::equal([ 'CZK', 'EUR' ], $default->getCurrencies());
		Tester\Assert::equal([], $default->getDomains());
		Tester\Assert::equal(TRUE, $default->isEnabled());

		Tester\Assert::equal('foo', $foo->getName());
		Tester\Assert::equal([ 'en_US' ], $foo->getLanguages());
		Tester\Assert::equal([ 'GB', 'USA' ], $foo->getCountries());
		Tester\Assert::equal([ 'EUR', 'USD', 'GBP' ], $foo->getCurrencies());
		Tester\Assert::equal([ 'example\.com\/foo\/' ], $foo->getDomains());
		Tester\Assert::equal(TRUE, $foo->isEnabled());

		Tester\Assert::equal('bar', $bar->getName());
		Tester\Assert::equal([ 'de_DE' ], $bar->getLanguages());
		Tester\Assert::equal([ 'DE' ], $bar->getCountries());
		Tester\Assert::equal([ 'EUR' ], $bar->getCurrencies());
		Tester\Assert::equal([ 'example\.com\/bar\/' ], $bar->getDomains());
		Tester\Assert::equal(FALSE, $bar->isEnabled());
	}

	/**
	 * @return void
	 */
	public function testProfileConfigurationWithoutDefaultProfileDefinition(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_without_default_profile_definition.neon');

		/** @var \SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer $profiles */
		$profiles = $container->getService('i18n.profile_container');

		# first profile is matched as default
		Tester\Assert::same($profiles->get(), $profiles->get('foo'));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingProfilesConfiguration(): void
	{
		Tester\Assert::exception(
			function () {
				$this->createContainer(__METHOD__);
			},
			SixtyEightPublishers\i18n\Exception\ConfigurationException::class,
			'You must define almost one profile in your configuration.'
		);
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingRequiredProfileSetting(): void
	{
		Tester\Assert::exception(
			function () {
				$this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_without_required_profile_setting.neon');
			},
			SixtyEightPublishers\i18n\Exception\ConfigurationException::class,
			'Please define almost one language for configuration key i18n.profiles.default.language'
		);
	}

	/**
	 * @return void
	 */
	public function testCustomStorageAndDetector(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_with_custom_storage_and_detector.neon');

		Tester\Assert::type(SixtyEightPublishers\i18n\Tests\Fixture\DummyProfileStorage::class, $container->getService('i18n.storage'));
		Tester\Assert::type(SixtyEightPublishers\i18n\Tests\Fixture\DummyDetector::class, $container->getService('i18n.detector'));
	}

	/**
	 * @return void
	 */
	public function testDebuggerEnabled(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_with_debugger_enabled.neon');

		/** @var \Tracy\Bar $bar */
		$bar = $container->getByType(Tracy\Bar::class);

		Tester\Assert::type(
			SixtyEightPublishers\i18n\Diagnostics\Panel::class,
			$bar->getPanel(SixtyEightPublishers\i18n\Diagnostics\Panel::class)
		);
	}

	/**
	 * @return void
	 */
	public function testTranslationsEnabled(): void
	{
		$container = $this->createContainer(__METHOD__, __DIR__ . '/../../files/i18n_with_translations_enabled.neon');

		Tester\Assert::type(SixtyEightPublishers\i18n\Translation\ProfileStorageResolver::class, $container->getService('i18n.translation_resolver'));
		# test chain resolver and dispatched event?
	}

	/**
	 * @param string            $method
	 * @param array|string|NULL $config
	 *
	 * @return \Nette\DI\Container
	 */
	private function createContainer(string $method, $config = NULL): Nette\DI\Container
	{
		return SixtyEightPublishers\i18n\Tests\Helper\ContainerFactory::createContainer(static::class . '.' . $method, $config);
	}
}

(new I18nExtensionIntegrationTest())->run();
