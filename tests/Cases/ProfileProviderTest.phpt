<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\i18n\ProfileProvider;
use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Tests\Fixture\DummyDetector;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

require __DIR__ . '/../bootstrap.php';

final class ProfileProviderTest extends TestCase
{
	/** @var \SixtyEightPublishers\i18n\ProfileProviderInterface */
	private $provider;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		$fooProfile = Mockery::mock(ProfileInterface::class);
		$barProfile = Mockery::mock(ProfileInterface::class);

		$storage = Mockery::mock(ProfileStorageInterface::class);
		$container = Mockery::mock(ProfileContainerInterface::class);
		$detector = new DummyDetector('foo');

		$fooProfile->shouldReceive('getLanguages')->andReturn([ 'cs_CZ', 'en_US' ]);
		$fooProfile->shouldReceive('getCountries')->andReturn([ 'CZ', 'GB' ]);
		$fooProfile->shouldReceive('getCurrencies')->andReturn([ 'CZK', 'GBP' ]);
		$fooProfile->shouldReceive('isEnabled')->andReturn(TRUE);
		$fooProfile->shouldReceive('getName')->andReturn('foo');

		$barProfile->shouldReceive('getLanguages')->andReturn([ 'cs_CZ', 'de_DE' ]);
		$barProfile->shouldReceive('getCountries')->andReturn([ 'CZ', 'DE' ]);
		$barProfile->shouldReceive('getCurrencies')->andReturn([ 'EUR' ]);
		$fooProfile->shouldReceive('getName')->andReturn('bar');

		$container->shouldReceive('toArray')->andReturn([ $fooProfile, $barProfile ]);
		$container->shouldReceive('get')->with()->andReturn($fooProfile);

		$storage->shouldReceive('makeActiveProfile')->with($fooProfile)->andReturn(new ActiveProfile(
			$fooProfile,
			new ActiveProfileChangeNotifier(),
			$storage
		));

		$this->provider = new ProfileProvider($detector, $storage, $container);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	/**
	 * @return void
	 */
	public function tesGetProfile(): void
	{
		Assert::noError(function () {
			$profile = $this->provider->getProfile();

			Assert::type(ActiveProfile::class, $profile);
		});
	}

	/**
	 * @return void
	 */
	public function testGetAllLanguages(): void
	{
		Assert::equal([ 'cs_CZ', 'en_US', 'de_DE' ], $this->provider->getAllLanguages());
	}

	/**
	 * @return void
	 */
	public function testGetAllCountries(): void
	{
		Assert::equal([ 'CZ', 'GB', 'DE' ], $this->provider->getAllCountries());
	}

	/**
	 * @return void
	 */
	public function testGetAllCurrencies(): void
	{
		Assert::equal([ 'CZK', 'GBP', 'EUR' ], $this->provider->getAllCurrencies());
	}
}

(new ProfileProviderTest())->run();
