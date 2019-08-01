<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Lists;

interface IList extends \ArrayAccess, \IteratorAggregate, \JsonSerializable, \Countable
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
