<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Storage;

use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;

interface ProfileStorageInterface
{
	/**
	 * @param \SixtyEightPublishers\i18n\Profile\ProfileInterface $profile
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\ActiveProfile
	 */
	public function makeActiveProfile(ProfileInterface $profile): ActiveProfile;

	/**
	 * @param \SixtyEightPublishers\i18n\Profile\ActiveProfile $profile
	 *
	 * @return void
	 */
	public function persistActiveProfile(ActiveProfile $profile): void;
}
