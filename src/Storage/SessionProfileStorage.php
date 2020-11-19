<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Storage;

use Nette\SmartObject;
use Nette\Http\Session;
use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Exception\InvalidArgumentException;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;

final class SessionProfileStorage implements ProfileStorageInterface
{
	use SmartObject;

	private const SESSION_SECTION = 'SixtyEightPublishers.Application';

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
	public function __construct(Session $session, ActiveProfileChangeNotifier $notifier)
	{
		$this->session = $session->getSection(self::SESSION_SECTION);
		$this->notifier = $notifier;
	}

	/**
	 * {@inheritdoc}
	 */
	public function makeActiveProfile(ProfileInterface $profile): ActiveProfile
	{
		$profile = new ActiveProfile($profile, $this->notifier, $this);

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
			} catch (InvalidArgumentException $e) {
				trigger_error($e->getMessage());
			}
		}

		return $profile;
	}

	/**
	 * {@inheritdoc}
	 */
	public function persistActiveProfile(ActiveProfile $profile): void
	{
		$this->session['profileName'] = $profile->getName();
		$this->session['profileCountry'] = $profile->getCountry();
		$this->session['profileLanguage'] = $profile->getLanguage();
		$this->session['profileCurrency'] = $profile->getCurrency();
	}
}
