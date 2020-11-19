<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Lists;

use ArrayIterator;
use LogicException;
use Nette\SmartObject;
use SixtyEightPublishers\i18n\Exception\RuntimeException;
use SixtyEightPublishers\i18n\Exception\InvalidArgumentException;

abstract class AbstractList implements ListInterface
{
	use SmartObject;

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

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->getList());
	}

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
			throw new InvalidArgumentException(sprintf(
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
		throw new LogicException('Changes of statically defined list is not allowed.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset): void
	{
		throw new LogicException('Changes of statically defined list is not allowed.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize(): array
	{
		return $this->getList();
	}

	/**
	 * {@inheritdoc}
	 */
	public function count(): int
	{
		return count($this->getList());
	}

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
		$language = ($language ?? $this->language) ?? $this->options->resolvedLanguage;

		if (isset($this->cached[$language])) {
			return $this->cached[$language];
		}

		$path = $this->createSourcePath($language);

		if (!file_exists($path)) {
			trigger_error(sprintf(
				'Missing lists for language %s, fallback %s is used.',
				$language,
				$this->options->fallbackLanguage
			), E_USER_NOTICE);

			$language = $this->options->fallbackLanguage;
			$path = $this->createSourcePath($language);
		}

		if (!file_exists($path)) {
			throw new RuntimeException('Can\'t resolve language for list.');
		}

		/** @noinspection PhpIncludeInspection */
		return $this->cached[$language] = include $path;
	}
}
