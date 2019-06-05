<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Translation;

use Kdyby;
use Nette;
use SixtyEightPublishers;

final class ProfileStorageResolver implements Kdyby\Translation\IUserLocaleResolver
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\i18n\IProfileProvider  */
	private $profileProvider;

	/** @var bool  */
	private $useDefault = FALSE;

	/** @var bool */
	private $lock = FALSE;

	/**
	 * @param \SixtyEightPublishers\i18n\IProfileProvider $profileProvider
	 * @param bool                                        $useDefault
	 */
	public function __construct(SixtyEightPublishers\i18n\IProfileProvider $profileProvider, bool $useDefault = FALSE)
	{
		$this->profileProvider = $profileProvider;
		$this->useDefault = $useDefault;
	}

	/*************** interface \Kdyby\Translation\IUserLocaleResolver ***************/

	/**
	 * @param \Kdyby\Translation\Translator $translator
	 *
	 * @return string
	 */
	public function resolve(Kdyby\Translation\Translator $translator): string
	{
		$profile = $this->profileProvider->getProfile();
		$locale = $profile->getLanguage($this->useDefault);

		if (NULL === $locale && FALSE === $this->lock) {
			$this->lock = TRUE;

			if (NULL !== ($newLocale = $translator->getLocale())) {
				$profile->changeLanguage($newLocale, FALSE);
			}

			$this->lock = FALSE;
		}

		return $locale;
	}
}
