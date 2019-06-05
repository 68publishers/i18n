<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Fixture;

use SixtyEightPublishers;

final class DummyProfileStorage implements SixtyEightPublishers\i18n\Storage\IProfileStorage
{
	/*************** interface \SixtyEightPublishers\i18n\Storage\IProfileStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function makeActiveProfile(SixtyEightPublishers\i18n\Profile\IProfile $profile): SixtyEightPublishers\i18n\Profile\ActiveProfile
	{
		throw new \RuntimeException('Not implemented, just test.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function persistActiveProfile(SixtyEightPublishers\i18n\Profile\ActiveProfile $profile): void
	{
	}
}
