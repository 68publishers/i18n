<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Storage;

use SixtyEightPublishers;

interface IProfileStorage
{
	/**
	 * @param \SixtyEightPublishers\i18n\Profile\IProfile $profile
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\ActiveProfile
	 */
	public function makeActiveProfile(SixtyEightPublishers\i18n\Profile\IProfile $profile): SixtyEightPublishers\i18n\Profile\ActiveProfile;

	/**
	 * @param \SixtyEightPublishers\i18n\Profile\ActiveProfile $profile
	 *
	 * @return void
	 */
	public function persistActiveProfile(SixtyEightPublishers\i18n\Profile\ActiveProfile $profile): void;
}
