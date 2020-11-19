<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Fixture;

use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;

final class DummyProfileStorage implements ProfileStorageInterface
{
	/*************** interface \SixtyEightPublishers\i18n\Storage\IProfileStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function makeActiveProfile(ProfileInterface $profile): ActiveProfile
	{
		throw new \RuntimeException('Not implemented, just test.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function persistActiveProfile(ActiveProfile $profile): void
	{
	}
}
