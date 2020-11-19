<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Lists;

use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;

interface ListInterface extends ArrayAccess, IteratorAggregate, JsonSerializable, Countable
{
	/**
	 * @param string $language
	 *
	 * @return void
	 */
	public function setLanguage(string $language): void;

	/**
	 * @param string|NULL $language
	 *
	 * @return array
	 */
	public function getList(?string $language = NULL): array;
}
