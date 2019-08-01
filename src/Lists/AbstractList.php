<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Lists;

use Nette;
use SixtyEightPublishers;

abstract class AbstractList implements IList
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\i18n\Lists\ListOptions  */
	private $options;

	/** @var NULL|string */
	private $language;

	/** @var array  */
	private $cached = [];

	/**
	 * @param \SixtyEightPublishers\i18n\Lists\ListOptions $options
	 */
	public function __construct(ListOptions $options)
	{
		$this->options = $options;
	}

	/**
	 * First %s is vendor dir, second %s is locale
	 *
	 * @return string
	 */
	abstract protected function getSourcePathMask(): string;

	/**
	 * @param string $language
	 *
	 * @return string
	 */
	private function createSourcePath(string $language): string
	{
		return sprintf(
			$this->getSourcePathMask(),
			$this->options->vendorDir,
			$language
		);
	}

	/************ interface \IteratorAggregate ************/

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->getList());
	}

	/************ interface \ArrayAccess ************/

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->getList()[$offset]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset): string
	{
		if (!$this->offsetExists($offset)) {
			throw new SixtyEightPublishers\i18n\Exception\InvalidArgumentException(sprintf(
				'Item %s is not defined in list.',
				(string) $offset
			));
		}

		return $this->getList()[$offset];
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		throw new \LogicException('Changes of statically defined list is not allowed.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset): void
	{
		throw new \LogicException('Changes of statically defined list is not allowed.');
	}

	/************ interface \JsonSerializable ************/

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize(): array
	{
		return $this->getList();
	}

	/************ interface \Countable ************/

	/**
	 * {@inheritdoc}
	 */
	public function count(): int
	{
		return count($this->getList());
	}

	/************ interface \SixtyEightPublishers\i18n\Lists\IList ************/

	/**
	 * {@inheritdoc}
	 */
	public function setLanguage(string $language): void
	{
		$this->language = $language;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getList(?string $language = NULL): array
	{
		$locale = ($language ?? $this->language) ?? $this->options->resolvedLanguage;

		if (isset($this->cached[$locale])) {
			return $this->cached[$locale];
		}

		if (NULL !== $language) {
			$path = $this->createSourcePath($locale);

			if (!file_exists($path)) {
				return $this->getList();
			}
		} else {
			$path = $this->createSourcePath($locale);

			if (!file_exists($path)) {
				$locale = $this->options->fallbackLanguage;
				$path = $this->createSourcePath($locale);
			}
		}

		if (!file_exists($path)) {
			throw new SixtyEightPublishers\i18n\Exception\RuntimeException('Can\'t resolve language for list.');
		}

		/** @noinspection PhpIncludeInspection */
		return $this->cached[$locale] = include $path;
	}
}
