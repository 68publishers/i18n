<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Fixture;

use SixtyEightPublishers;

final class DummyDetector implements SixtyEightPublishers\i18n\Detector\IDetector
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
	public function detect(SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer $profileContainer): ?SixtyEightPublishers\i18n\Profile\IProfile
	{
		return NULL;
	}
}
