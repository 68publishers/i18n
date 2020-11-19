<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Translation;

use Nette\SmartObject;
use Kdyby\Translation\Translator;
use Kdyby\Translation\IUserLocaleResolver;
use SixtyEightPublishers\i18n\ProfileProviderInterface;

final class ProfileStorageResolver implements IUserLocaleResolver
{
	use SmartObject;

	/** @var \SixtyEightPublishers\i18n\ProfileProviderInterface  */
	private $profileProvider;

	/** @var bool  */
	private $useDefault = FALSE;

	/** @var bool */
	private $lock = FALSE;

	/**
	 * @param \SixtyEightPublishers\i18n\ProfileProviderInterface $profileProvider
	 * @param bool                                                $useDefault
	 */
	public function __construct(ProfileProviderInterface $profileProvider, bool $useDefault = FALSE)
	{
		$this->profileProvider = $profileProvider;
		$this->useDefault = $useDefault;
	}

	/**
	 * @param \Kdyby\Translation\Translator $translator
	 *
	 * @return string
	 */
	public function resolve(Translator $translator): string
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
