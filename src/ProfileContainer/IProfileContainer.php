<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\ProfileContainer;

use SixtyEightPublishers;

interface IProfileContainer extends \IteratorAggregate
{
	/**
	 * NULL === default
	 *
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\IProfile
	 */
	public function get(?string $name = NULL): SixtyEightPublishers\i18n\Profile\IProfile;

	/**
	 * @return \SixtyEightPublishers\i18n\Profile\IProfile[]
	 */
	public function toArray(): array;
}
