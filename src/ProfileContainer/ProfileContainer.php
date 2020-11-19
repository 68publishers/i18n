<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\ProfileContainer;

use ArrayIterator;
use Nette\SmartObject;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Exception\InvalidArgumentException;

final class ProfileContainer implements ProfileContainerInterface
{
	use SmartObject;

	/** @var \SixtyEightPublishers\i18n\Profile\ProfileInterface  */
	private $defaultProfile;

	/** @var \SixtyEightPublishers\i18n\Profile\ProfileInterface[] */
	private $profiles = [];

	/**
	 * @param \SixtyEightPublishers\i18n\Profile\ProfileInterface   $defaultProfile
	 * @param \SixtyEightPublishers\i18n\Profile\ProfileInterface[] $profiles
	 */
	public function __construct(ProfileInterface $defaultProfile, array $profiles)
	{
		$this->defaultProfile = $defaultProfile;

		$this->addProfile($defaultProfile);

		foreach ($profiles as $profile) {
			$this->addProfile($profile);
		}
	}

	/**
	 * @param \SixtyEightPublishers\i18n\Profile\ProfileInterface $profile
	 *
	 * @return void
	 */
	private function addProfile(ProfileInterface $profile): void
	{
		$this->profiles[$profile->getName()] = $profile;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(?string $name = NULL): ProfileInterface
	{
		if (NULL === $name) {
			return $this->defaultProfile;
		}

		if (!isset($this->profiles[$name])) {
			throw new InvalidArgumentException(sprintf(
				'Profile with name "%s" is not defined.',
				$name
			));
		}

		return $this->profiles[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function toArray(): array
	{
		return $this->profiles;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->toArray());
	}
}
