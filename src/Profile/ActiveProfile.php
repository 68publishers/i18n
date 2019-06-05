<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Profile;

use Nette;
use SixtyEightPublishers;

/**
 * @property-read string $name
 * @property-read NULL|string    $country
 * @property-read NULL|string    $language
 * @property-read NULL|string    $currency
 * @property-read string    $defaultCountry
 * @property-read string    $defaultLanguage
 * @property-read string    $defaultCurrency
 */
final class ActiveProfile implements IProfile
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\i18n\Profile\IProfile  */
	private $profile;

	/** @var \SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier  */
	private $notifier;

	/** @var \SixtyEightPublishers\i18n\Storage\IProfileStorage  */
	private $profileStorage;

	/** @var NULL|string */
	private $country;

	/** @var NULL|string */
	private $language;

	/** @var NULL|string */
	private $currency;

	/** @var string */
	private $defaultCountry;

	/** @var string */
	private $defaultLanguage;

	/** @var string */
	private $defaultCurrency;

	/**
	 * @param \SixtyEightPublishers\i18n\Profile\IProfile                    $profile
	 * @param \SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier $notifier
	 * @param \SixtyEightPublishers\i18n\Storage\IProfileStorage             $profileStorage
	 */
	public function __construct(
		IProfile $profile,
		ActiveProfileChangeNotifier $notifier,
		SixtyEightPublishers\i18n\Storage\IProfileStorage $profileStorage
	) {
		if (FALSE === $profile->isEnabled()) {
			throw new SixtyEightPublishers\i18n\Exception\InvalidArgumentException(sprintf(
				'Profile "%s" can\'t be set as active because its disabled.',
				$profile->getName()
			));
		}

		if (0 >= count($profile->getCountries()) || 0 >= count($profile->getLanguages()) || 0 >= count($profile->getCurrencies())) {
			throw new SixtyEightPublishers\i18n\Exception\InvalidArgumentException(sprintf(
				'Invalid profile "%s" passed, profile must contains almost one country, language and currency.',
				$profile->getName()
			));
		}

		$this->profile = $profile;
		$this->notifier = $notifier;
		$this->profileStorage = $profileStorage;
		$this->defaultCountry = $profile->getCountries()[0];
		$this->defaultLanguage = $profile->getLanguages()[0];
		$this->defaultCurrency = $profile->getCurrencies()[0];
	}

	/**
	 * @param bool $useDefault
	 *
	 * @return NULL|string
	 */
	public function getCountry(bool $useDefault = TRUE): ?string
	{
		return TRUE === $useDefault ? ($this->country ?? $this->defaultCountry) : $this->country;
	}

	/**
	 * @param bool $useDefault
	 *
	 * @return NULL|string
	 */
	public function getLanguage(bool $useDefault = TRUE): ?string
	{
		return TRUE === $useDefault ? ($this->language ?? $this->defaultLanguage) : $this->language;
	}

	/**
	 * @param bool $useDefault
	 *
	 * @return NULL|string
	 */
	public function getCurrency(bool $useDefault = TRUE): ?string
	{
		return TRUE === $useDefault ? ($this->currency ?? $this->defaultCurrency) : $this->currency;
	}

	/**
	 * @return string
	 */
	public function getDefaultCountry(): string
	{
		return $this->defaultCountry;
	}

	/**
	 * @return string
	 */
	public function getDefaultLanguage(): string
	{
		return $this->defaultLanguage;
	}

	/**
	 * @return string
	 */
	public function getDefaultCurrency(): string
	{
		return $this->defaultCurrency;
	}

	/**
	 * @param string $country
	 * @param bool   $persist
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\ActiveProfile
	 * @throws \SixtyEightPublishers\i18n\Exception\InvalidArgumentException
	 */
	public function changeCountry(string $country, bool $persist = TRUE): self
	{
		if (!in_array($country, $this->getCountries())) {
			throw new SixtyEightPublishers\i18n\Exception\InvalidArgumentException(sprintf(
				'Country with code "%s" is not defined in active profile.',
				$country
			));
		}

		$this->country = $country;

		if (TRUE === $persist) {
			$this->profileStorage->persistActiveProfile($this);
		}

		$this->notifier->notifyOnCountryChange($this);

		return $this;
	}

	/**
	 * @param string $language
	 * @param bool   $persist
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\ActiveProfile
	 * @throws \SixtyEightPublishers\i18n\Exception\InvalidArgumentException
	 */
	public function changeLanguage(string $language, bool $persist = TRUE): self
	{
		if (!in_array($language, $this->getLanguages())) {
			if (is_string($language)) {
				foreach ($this->getLanguages() as $available) {
					if (substr($available, 0, 2) === substr($language, 0, 2)) {
						return $this->changeLanguage($available, $persist);
					}
				}
			}

			throw new SixtyEightPublishers\i18n\Exception\InvalidArgumentException(sprintf(
				'Language with code "%s" is not defined in active profile.',
				$language
			));
		}

		$this->language = $language;

		if (TRUE === $persist) {
			$this->profileStorage->persistActiveProfile($this);
		}

		$this->notifier->notifyOnLanguageChange($this);

		return $this;
	}

	/**
	 * @param string $currency
	 * @param bool   $persist
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\ActiveProfile
	 * @throws \SixtyEightPublishers\i18n\Exception\InvalidArgumentException
	 */
	public function changeCurrency(string $currency, bool $persist = TRUE): self
	{
		if (!in_array($currency, $this->getCurrencies())) {
			throw new SixtyEightPublishers\i18n\Exception\InvalidArgumentException(sprintf(
				'Currency with code "%s" is not defined in active profile.',
				$currency
			));
		}

		$this->currency = $currency;

		if (TRUE === $persist) {
			$this->profileStorage->persistActiveProfile($this);
		}

		$this->notifier->notifyOnCurrencyChange($this);

		return $this;
	}

	/***************** interface \SixtyEightPublishers\i18n\Profile\Profile\IProfile *****************/

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return $this->profile->getName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCountries(): array
	{
		return $this->profile->getCountries();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLanguages(): array
	{
		return $this->profile->getLanguages();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCurrencies(): array
	{
		return $this->profile->getCurrencies();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDomains(): array
	{
		return $this->profile->getDomains();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEnabled(): bool
	{
		return $this->profile->isEnabled();
	}
}
