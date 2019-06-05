<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Storage;

use Nette;
use SixtyEightPublishers;

final class SessionProfileStorage implements IProfileStorage
{
	use Nette\SmartObject;

	const SESSION_SECTION = 'SixtyEightPublishers.Application';

	/** @var NULL|\SixtyEightPublishers\i18n\Profile\ActiveProfile */
	private $profile;

	/** @var \Nette\Http\SessionSection  */
	private $session;

	/** @var \SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier  */
	private $notifier;

	/**
	 * @param \Nette\Http\Session                                            $session
	 * @param \SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier $notifier
	 */
	public function __construct(Nette\Http\Session $session, SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier $notifier)
	{
		$this->session = $session->getSection(self::SESSION_SECTION);
		$this->notifier = $notifier;
	}

	/************* interface \SixtyEightPublishers\i18n\Storage\IProfileStorage *************/

	/**
	 * {@inheritdoc}
	 */
	public function makeActiveProfile(SixtyEightPublishers\i18n\Profile\IProfile $profile): SixtyEightPublishers\i18n\Profile\ActiveProfile
	{
		$profile = new SixtyEightPublishers\i18n\Profile\ActiveProfile($profile, $this->notifier, $this);

		if ($profile->getName() !== $this->session['profileName']) {
			$this->session['profileName'] = $profile->getName();

			foreach ([ 'profileCountry', 'profileLanguage', 'profileCurrency' ] as $item) {
				if (isset($this->session[$item])) {
					unset($this->session[$item]);
				}
			}

			return $profile;
		}

		foreach ([ 'changeCountry' => 'profileCountry', 'changeLanguage' => 'profileLanguage', 'changeCurrency' => 'profileCurrency'] as $method => $item) {
			if (!isset($this->session[$item])) {
				continue;
			}

			try {
				$profile->{$method}($this->session[$item], FALSE);
			} catch (SixtyEightPublishers\i18n\Exception\InvalidArgumentException $e) {
				trigger_error($e->getMessage());
			}
		}

		return $profile;
	}

	/**
	 * {@inheritdoc}
	 */
	public function persistActiveProfile(SixtyEightPublishers\i18n\Profile\ActiveProfile $profile): void
	{
		$this->session['profileName'] = $profile->getName();
		$this->session['profileCountry'] = $profile->getCountry();
		$this->session['profileLanguage'] = $profile->getLanguage();
		$this->session['profileCurrency'] = $profile->getCurrency();
	}
}
