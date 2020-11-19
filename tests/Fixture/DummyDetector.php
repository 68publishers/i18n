<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Fixture;

use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Detector\DetectorInterface;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

final class DummyDetector implements DetectorInterface
{
	/**
	 * @param mixed $foo
	 */
	public function __construct($foo)
	{
	}

	/*************** interface \SixtyEightPublishers\i18n\Detector\IDetector ***************/

	/**
	 * {@inheritdoc}
	 */
	public function detect(ProfileContainerInterface $profileContainer): ?ProfileInterface
	{
		return NULL;
	}
}
