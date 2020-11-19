<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Detector;

use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

interface DetectorInterface
{
	/**
	 * If method does not return instance of \SixtyEightPublishers\i18n\Profile\Profile, the default profile will be set automatically.
	 *
	 * @param \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface $profileContainer
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\ProfileInterface|NULL
	 */
	public function detect(ProfileContainerInterface $profileContainer): ?ProfileInterface;
}
