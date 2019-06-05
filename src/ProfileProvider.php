<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n;

use Nette;
use SixtyEightPublishers;

/**
 * @property-read \SixtyEightPublishers\i18n\Profile\ActiveProfile $profile
 * @property-read array $allLanguages
 * @property-read array $allCountries
 * @property-read array $allCurrencies
 */
final class ProfileProvider implements IProfileProvider
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\i18n\Detector\IDetector  */
	private $detector;

	/** @var \SixtyEightPublishers\i18n\Storage\IProfileStorage  */
	private $profileStorage;

	/** @var \SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer  */
	private $profileContainer;

	/** @var NULL|\SixtyEightPublishers\i18n\Profile\ActiveProfile */
	private $profile;

	/** @var NULL|array */
	private $languages;

	/** @var NULL|array */
	private $countries;

	/** @var NULL|array */
	private $currencies;

	/**
	 * @param \SixtyEightPublishers\i18n\Detector\IDetector                 $detector
	 * @param \SixtyEightPublishers\i18n\Storage\IProfileStorage            $profileStorage
	 * @param \SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer $profileContainer
	 */
	public function __construct(
		Detector\IDetector $detector,
		Storage\IProfileStorage $profileStorage,
		ProfileContainer\IProfileContainer $profileContainer
	) {
		$this->detector = $detector;
		$this->profileStorage = $profileStorage;
		$this->profileContainer = $profileContainer;
	}

	/**
	 * @param callable $cb
	 *
	 * @return array
	 */
	private function getUniqueElements(callable $cb): array
	{
		return array_values(array_unique(array_merge(...array_map($cb, $this->profileContainer->toArray()))));
	}

	/************* interface \SixtyEightPublishers\i18n\IProfileProvider *************/

	/**
	 * {@inheritdoc}
	 */
	public function getProfile(): SixtyEightPublishers\i18n\Profile\ActiveProfile
	{
		if (NULL !== $this->profile) {
			return $this->profile;
		}

		return $this->profile = $this->profileStorage->makeActiveProfile(
			$this->detector->detect($this->profileContainer) ?? $this->profileContainer->get()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllLanguages(): array
	{
		if (is_array($this->languages)) {
			return $this->languages;
		}

		return $this->languages = $this->getUniqueElements(function (Profile\IProfile $profile) {
			return $profile->getLanguages();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllCountries(): array
	{
		if (is_array($this->countries)) {
			return $this->countries;
		}

		return $this->countries = $this->getUniqueElements(function (Profile\IProfile $profile) {
			return $profile->getCountries();
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllCurrencies(): array
	{
		if (is_array($this->currencies)) {
			return $this->currencies;
		}

		return $this->currencies = $this->getUniqueElements(function (Profile\IProfile $profile) {
			return $profile->getCurrencies();
		});
	}
}
