<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Detector;

use SixtyEightPublishers;

interface IDetector
{
	/**
	 * If method does not return instance of \SixtyEightPublishers\i18n\Profile\Profile, the default profile will be set automatically.
	 *
	 * @param \SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer $profileContainer
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\IProfile|NULL
	 */
	public function detect(SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer $profileContainer): ?SixtyEightPublishers\i18n\Profile\IProfile;
}
