<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Lists;

use Throwable;
use Nette\SmartObject;
use SixtyEightPublishers\i18n\ProfileProviderInterface;

/**
 * @property-read string $vendorDir
 * @property-read string $fallbackLanguage
 * @property-read string|NULL $defaultLanguage
 * @property-read string $resolvedLanguage
 */
final class ListOptions
{
	use SmartObject;

	/** @var \SixtyEightPublishers\i18n\ProfileProviderInterface  */
	private $profileProvider;

	/** @var string  */
	private $vendorDir;

	/** @var string  */
	private $fallbackLanguage;

	/** @var string|NULL  */
	private $defaultLanguage;

	/**
	 * @param \SixtyEightPublishers\i18n\ProfileProviderInterface $profileProvider
	 * @param string                                              $vendorDir
	 * @param string                                              $fallbackLanguage
	 * @param string|NULL                                         $defaultLanguage
	 */
	public function __construct(
		ProfileProviderInterface $profileProvider,
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
		} catch (Throwable $e) {
			trigger_error($e->getMessage(), E_USER_NOTICE);

			return $this->fallbackLanguage;
		}
	}
}
