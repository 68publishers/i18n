<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Translation;

use Nette\Localization\ITranslator;
use SixtyEightPublishers\i18n\ProfileProviderInterface;
use SixtyEightPublishers\TranslationBridge\Localization\TranslatorLocaleResolverInterface;

final class TranslatorLocaleResolver implements TranslatorLocaleResolverInterface
{
	/** @var \SixtyEightPublishers\i18n\ProfileProviderInterface  */
	private $profileProvider;

	/** @var bool  */
	private $useDefault;

	/**
	 * @param \SixtyEightPublishers\i18n\ProfileProviderInterface $profileProvider
	 * @param bool                                                $useDefault
	 */
	public function __construct(ProfileProviderInterface $profileProvider, bool $useDefault)
	{
		$this->profileProvider = $profileProvider;
		$this->useDefault = $useDefault;
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolveLocale(ITranslator $translator): ?string
	{
		return $this->profileProvider->getProfile()->getLanguage($this->useDefault);
	}
}
