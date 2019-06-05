<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Tester;
use Mockery;
use SixtyEightPublishers;

require __DIR__ . '/../bootstrap.php';

final class ProfileProviderTest extends Tester\TestCase
{
	/** @var \SixtyEightPublishers\i18n\IProfileProvider */
	private $provider;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		$fooProfile = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);
		$barProfile = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);

		$storage = Mockery::mock(SixtyEightPublishers\i18n\Storage\IProfileStorage::class);
		$container = Mockery::mock(SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer::class);
		$detector = new SixtyEightPublishers\i18n\Tests\Fixture\DummyDetector('foo');

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

		$storage->shouldReceive('makeActiveProfile')->with($fooProfile)->andReturn(new SixtyEightPublishers\i18n\Profile\ActiveProfile(
			$fooProfile,
			new SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier(),
			$storage
		));

		$this->provider = new SixtyEightPublishers\i18n\ProfileProvider($detector, $storage, $container);
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
		Tester\Assert::noError(function () {
			$profile = $this->provider->getProfile();

			Tester\Assert::type(SixtyEightPublishers\i18n\Profile\ActiveProfile::class, $profile);
		});
	}

	/**
	 * @return void
	 */
	public function testGetAllLanguages(): void
	{
		Tester\Assert::equal([ 'cs_CZ', 'en_US', 'de_DE' ], $this->provider->getAllLanguages());
	}

	/**
	 * @return void
	 */
	public function testGetAllCountries(): void
	{
		Tester\Assert::equal([ 'CZ', 'GB', 'DE' ], $this->provider->getAllCountries());
	}

	/**
	 * @return void
	 */
	public function testGetAllCurrencies(): void
	{
		Tester\Assert::equal([ 'CZK', 'GBP', 'EUR' ], $this->provider->getAllCurrencies());
	}
}

(new ProfileProviderTest())->run();
