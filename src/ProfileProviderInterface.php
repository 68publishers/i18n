<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n;

use SixtyEightPublishers\i18n\Profile\ActiveProfile;

interface ProfileProviderInterface
{
	/**
	 * @return \SixtyEightPublishers\i18n\Profile\ActiveProfile
	 */
	public function getProfile(): ActiveProfile;

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
