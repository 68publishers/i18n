<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Profile;

use Nette\SmartObject;

final class Profile implements ProfileInterface
{
	use SmartObject;

	/** @var string  */
	private $name;

	/** @var array  */
	private $languages;

	/** @var array  */
	private $countries;

	/** @var array  */
	private $currencies;

	/** @var array  */
	private $domains;

	/** @var bool  */
	private $enabled;

	/**
	 * @param string   $name
	 * @param string[] $languages
	 * @param string[] $countries
	 * @param string[] $currencies
	 * @param string[] $domains
	 * @param bool     $enabled
	 */
	public function __construct(string $name, array $languages, array $countries, array $currencies, array $domains, bool $enabled = TRUE)
	{
		$this->name = $name;
		$this->languages = $languages;
		$this->countries = $countries;
		$this->currencies = $currencies;
		$this->domains = $domains;
		$this->enabled = $enabled;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getCountries(): array
	{
		return $this->countries;
	}

	/**
	 * @return array
	 */
	public function getLanguages(): array
	{
		return $this->languages;
	}

	/**
	 * @return array
	 */
	public function getCurrencies(): array
	{
		return $this->currencies;
	}

	/**
	 * @return array
	 */
	public function getDomains(): array
	{
		return $this->domains;
	}

	/**
	 * @return bool
	 */
	public function isEnabled(): bool
	{
		return $this->enabled;
	}
}
