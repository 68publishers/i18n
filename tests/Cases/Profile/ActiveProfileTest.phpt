<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\Exception\InvalidArgumentException;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;

require __DIR__ . '/../../bootstrap.php';

final class ActiveProfileTest extends TestCase
{
	/** @var NULL|\SixtyEightPublishers\i18n\Profile\ActiveProfile */
	private $activeProfile;

	/**
	 * {@inheritdoc}
	 */
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$profile = Mockery::mock(ProfileInterface::class);
		$storage = Mockery::mock(ProfileStorageInterface::class);

		$profile->shouldReceive('getName')->andReturn('foo');
		$profile->shouldReceive('getCountries')->andReturn([ 'CZ', 'GB' ]);
		$profile->shouldReceive('getLanguages')->andReturn([ 'cs_CZ', 'en_US' ]);
		$profile->shouldReceive('getCurrencies')->andReturn([ 'CZK', 'GBP' ]);
		$profile->shouldReceive('getDomains')->andReturn([ 'example\.com\/foo\/' ]);
		$profile->shouldReceive('isEnabled')->andReturn(TRUE);

		$this->activeProfile = new ActiveProfile($profile, new ActiveProfileChangeNotifier(), $storage);

		$storage->shouldReceive('persistActiveProfile')->with($this->activeProfile)->andReturnNull();
	}

	/**
	 * @return void
	 */
	public function testProfileGetters(): void
	{
		Assert::equal('foo', $this->activeProfile->getName());
		Assert::equal([ 'cs_CZ', 'en_US' ], $this->activeProfile->getLanguages());
		Assert::equal([ 'CZ', 'GB' ], $this->activeProfile->getCountries());
		Assert::equal([ 'CZK', 'GBP' ], $this->activeProfile->getCurrencies());
		Assert::equal([ 'example\.com\/foo\/' ], $this->activeProfile->getDomains());
		Assert::equal(TRUE, $this->activeProfile->isEnabled());
	}

	/**
	 * @return void
	 */
	public function testDefaults(): void
	{
		Assert::equal('cs_CZ', $this->activeProfile->getDefaultLanguage());
		Assert::equal('CZ', $this->activeProfile->getDefaultCountry());
		Assert::equal('CZK', $this->activeProfile->getDefaultCurrency());
	}

	/**
	 * @return void
	 */
	public function testValidCountryChanged(): void
	{
		Assert::noError(function () {
			$this->activeProfile->changeCountry('GB');
		});

		Assert::equal('GB', $this->activeProfile->getCountry(FALSE));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionWhenInvalidCountryChanged(): void
	{
		Assert::exception(
			function () {
				$this->activeProfile->changeCountry('DE');
			},
			InvalidArgumentException::class,
			'Country with code "DE" is not defined in active profile.'
		);
	}

	/**
	 * @return void
	 */
	public function testValidCurrencyChanged(): void
	{
		Assert::noError(function () {
			$this->activeProfile->changeCurrency('GBP');
		});

		Assert::equal('GBP', $this->activeProfile->getCurrency(FALSE));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionWhenInvalidCurrencyChanged(): void
	{
		Assert::exception(
			function () {
				$this->activeProfile->changeCurrency('EUR');
			},
			InvalidArgumentException::class,
			'Currency with code "EUR" is not defined in active profile.'
		);
	}

	/**
	 * @return void
	 */
	public function testValidLanguageChanged(): void
	{
		Assert::noError(function () {
			$this->activeProfile->changeLanguage('en_US');
		});
		Assert::equal('en_US', $this->activeProfile->getLanguage(FALSE));

		# set two latter code
		Assert::noError(function () {
			$this->activeProfile->changeLanguage('cs');
		});
		Assert::equal('cs_CZ', $this->activeProfile->getLanguage(FALSE));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionWhenInvalidLanguageChanged(): void
	{
		Assert::exception(
			function () {
				$this->activeProfile->changeLanguage('de_DE');
			},
			InvalidArgumentException::class,
			'Language with code "de_DE" is not defined in active profile.'
		);
	}
}

(new ActiveProfileTest())->run();
