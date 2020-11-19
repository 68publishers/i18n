<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\ProfileContainer;

use SixtyEightPublishers\i18n\Profile\ProfileInterface;

interface ProfileContainerInterface extends \IteratorAggregate
{
	/**
	 * NULL === default
	 *
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\i18n\Profile\ProfileInterface
	 */
	public function get(?string $name = NULL): ProfileInterface;

	/**
	 * @return \SixtyEightPublishers\i18n\Profile\ProfileInterface[]
	 */
	public function toArray(): array;
}
