<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n;

use SixtyEightPublishers;

interface IProfileProvider
{
	/**
	 * @return \SixtyEightPublishers\i18n\Profile\ActiveProfile
	 */
	public function getProfile(): SixtyEightPublishers\i18n\Profile\ActiveProfile;

	/**
	 * @return string[]
	 */
	public function getAllLanguages(): array;

	/**
	 * @return string[]
	 */
	public function getAllCountries(): array;

	/**
	 * @return string[]
	 */
	public function getAllCurrencies(): array;
}
