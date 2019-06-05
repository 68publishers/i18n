<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\ProfileContainer;

use Nette;
use SixtyEightPublishers;

final class ProfileContainer implements IProfileContainer
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\i18n\Profile\IProfile  */
	private $defaultProfile;

	/** @var \SixtyEightPublishers\i18n\Profile\IProfile[] */
	private $profiles = [];

	/**
	 * @param \SixtyEightPublishers\i18n\Profile\IProfile   $defaultProfile
	 * @param \SixtyEightPublishers\i18n\Profile\IProfile[] $profiles
	 */
	public function __construct(SixtyEightPublishers\i18n\Profile\IProfile $defaultProfile, array $profiles)
	{
		$this->defaultProfile = $defaultProfile;

		$this->addProfile($defaultProfile);

		foreach ($profiles as $profile) {
			$this->addProfile($profile);
		}
	}

	/**
	 * @param \SixtyEightPublishers\i18n\Profile\IProfile $profile
	 *
	 * @return void
	 */
	private function addProfile(SixtyEightPublishers\i18n\Profile\IProfile $profile): void
	{
		$this->profiles[$profile->getName()] = $profile;
	}

	/************* interface \SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer *************/

	/**
	 * {@inheritdoc}
	 */
	public function get(?string $name = NULL): SixtyEightPublishers\i18n\Profile\IProfile
	{
		if (NULL === $name) {
			return $this->defaultProfile;
		}

		if (!isset($this->profiles[$name])) {
			throw new SixtyEightPublishers\i18n\Exception\InvalidArgumentException(sprintf(
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
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->toArray());
	}
}
