<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n;

use Nette\SmartObject;
use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Detector\DetectorInterface;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

/**
 * @property-read \SixtyEightPublishers\i18n\Profile\ActiveProfile $profile
 * @property-read array $allLanguages
 * @property-read array $allCountries
 * @property-read array $allCurrencies
 */
final class ProfileProvider implements ProfileProviderInterface
{
	use SmartObject;

	/** @var \SixtyEightPublishers\i18n\Detector\DetectorInterface  */
	private $detector;

	/** @var \SixtyEightPublishers\i18n\Storage\ProfileStorageInterface  */
	private $profileStorage;

	/** @var \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface  */
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
	 * @param \SixtyEightPublishers\i18n\Detector\DetectorInterface                 $detector
	 * @param \SixtyEightPublishers\i18n\Storage\ProfileStorageInterface            $profileStorage
	 * @param \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface $profileContainer
	 */
	public function __construct(
		DetectorInterface $detector,
		ProfileStorageInterface $profileStorage,
		ProfileContainerInterface $profileContainer
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
		return array_values(array_unique(array_merge(...array_values(array_map($cb, $this->profileContainer->toArray())))));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProfile(): ActiveProfile
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

		return $this->languages = $this->getUniqueElements(static function (ProfileInterface $profile) {
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

		return $this->countries = $this->getUniqueElements(static function (ProfileInterface $profile) {
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

		return $this->currencies = $this->getUniqueElements(static function (ProfileInterface $profile) {
			return $profile->getCurrencies();
		});
	}
}
