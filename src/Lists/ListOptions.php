<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Lists;

use Nette;
use SixtyEightPublishers;

/**
 * @property-read string $vendorDir
 * @property-read string $fallbackLanguage
 * @property-read string|NULL $defaultLanguage
 * @property-read string $resolvedLanguage
 */
final class ListOptions
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\i18n\IProfileProvider  */
	private $profileProvider;

	/** @var string  */
	private $vendorDir;

	/** @var string  */
	private $fallbackLanguage;

	/** @var string|NULL  */
	private $defaultLanguage;

	/**
	 * @param \SixtyEightPublishers\i18n\IProfileProvider $profileProvider
	 * @param string                                      $vendorDir
	 * @param string                                      $fallbackLanguage
	 * @param string|NULL                                 $defaultLanguage
	 */
	public function __construct(
		SixtyEightPublishers\i18n\IProfileProvider $profileProvider,
		string $vendorDir,
		string $fallbackLanguage,
		?string $defaultLanguage
	) {
		$this->profileProvider = $profileProvider;
		$this->vendorDir = $vendorDir;
		$this->fallbackLanguage = $fallbackLanguage;
		$this->defaultLanguage = $defaultLanguage;
	}

	/**
	 * @return string
	 */
	public function getVendorDir(): string
	{
		return $this->vendorDir;
	}

	/**
	 * @return string
	 */
	public function getFallbackLanguage(): string
	{
		return $this->fallbackLanguage;
	}

	/**
	 * @return NULL|string
	 */
	public function getDefaultLanguage(): ?string
	{
		return $this->defaultLanguage;
	}

	/**
	 * @return string
	 */
	public function getResolvedLanguage(): string
	{
		try {
			return $this->defaultLanguage ?? $this->profileProvider->getProfile()->language;
		} catch (\Throwable $e) {
			trigger_error($e->getMessage(), E_USER_NOTICE);

			return $this->fallbackLanguage;
		}
	}
}
