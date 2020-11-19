<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Profile;

interface ProfileInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return array
	 */
	public function getCountries(): array;

	/**
	 * @return array
	 */
	public function getLanguages(): array;

	/**
	 * @return array
	 */
	public function getCurrencies(): array;

	/**
	 * @return array
	 */
	public function getDomains(): array;

	/**
	 * @return bool
	 */
	public function isEnabled(): bool;
}
