<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Tester;
use Mockery;
use SixtyEightPublishers;

require __DIR__ . '/../../bootstrap.php';

final class ActiveProfileTest extends Tester\TestCase
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

		$profile = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);
		$storage = Mockery::mock(SixtyEightPublishers\i18n\Storage\IProfileStorage::class);

		$profile->shouldReceive('getName')->andReturn('foo');
		$profile->shouldReceive('getCountries')->andReturn([ 'CZ', 'GB' ]);
		$profile->shouldReceive('getLanguages')->andReturn([ 'cs_CZ', 'en_US' ]);
		$profile->shouldReceive('getCurrencies')->andReturn([ 'CZK', 'GBP' ]);
		$profile->shouldReceive('getDomains')->andReturn([ 'example\.com\/foo\/' ]);
		$profile->shouldReceive('isEnabled')->andReturn(TRUE);

		$this->activeProfile = new SixtyEightPublishers\i18n\Profile\ActiveProfile($profile, new SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier(), $storage);

		$storage->shouldReceive('persistActiveProfile')->with($this->activeProfile)->andReturnNull();
	}

	/**
	 * @return void
	 */
	public function testProfileGetters(): void
	{
		Tester\Assert::equal('foo', $this->activeProfile->getName());
		Tester\Assert::equal([ 'cs_CZ', 'en_US' ], $this->activeProfile->getLanguages());
		Tester\Assert::equal([ 'CZ', 'GB' ], $this->activeProfile->getCountries());
		Tester\Assert::equal([ 'CZK', 'GBP' ], $this->activeProfile->getCurrencies());
		Tester\Assert::equal([ 'example\.com\/foo\/' ], $this->activeProfile->getDomains());
		Tester\Assert::equal(TRUE, $this->activeProfile->isEnabled());
	}

	/**
	 * @return void
	 */
	public function testDefaults(): void
	{
		Tester\Assert::equal('cs_CZ', $this->activeProfile->getDefaultLanguage());
		Tester\Assert::equal('CZ', $this->activeProfile->getDefaultCountry());
		Tester\Assert::equal('CZK', $this->activeProfile->getDefaultCurrency());
	}

	/**
	 * @return void
	 */
	public function testValidCountryChanged(): void
	{
		Tester\Assert::noError(function () {
			$this->activeProfile->changeCountry('GB');
		});

		Tester\Assert::equal('GB', $this->activeProfile->getCountry(FALSE));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionWhenInvalidCountryChanged(): void
	{
		Tester\Assert::exception(
			function () {
				$this->activeProfile->changeCountry('DE');
			},
			SixtyEightPublishers\i18n\Exception\InvalidArgumentException::class,
			'Country with code "DE" is not defined in active profile.'
		);
	}

	/**
	 * @return void
	 */
	public function testValidCurrencyChanged(): void
	{
		Tester\Assert::noError(function () {
			$this->activeProfile->changeCurrency('GBP');
		});

		Tester\Assert::equal('GBP', $this->activeProfile->getCurrency(FALSE));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionWhenInvalidCurrencyChanged(): void
	{
		Tester\Assert::exception(
			function () {
				$this->activeProfile->changeCurrency('EUR');
			},
			SixtyEightPublishers\i18n\Exception\InvalidArgumentException::class,
			'Currency with code "EUR" is not defined in active profile.'
		);
	}

	/**
	 * @return void
	 */
	public function testValidLanguageChanged(): void
	{
		Tester\Assert::noError(function () {
			$this->activeProfile->changeLanguage('en_US');
		});
		Tester\Assert::equal('en_US', $this->activeProfile->getLanguage(FALSE));

		# set two latter code
		Tester\Assert::noError(function () {
			$this->activeProfile->changeLanguage('cs');
		});
		Tester\Assert::equal('cs_CZ', $this->activeProfile->getLanguage(FALSE));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionWhenInvalidLanguageChanged(): void
	{
		Tester\Assert::exception(
			function () {
				$this->activeProfile->changeLanguage('de_DE');
			},
			SixtyEightPublishers\i18n\Exception\InvalidArgumentException::class,
			'Language with code "de_DE" is not defined in active profile.'
		);
	}
}

(new ActiveProfileTest())->run();
