<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Profile;

use Nette\SmartObject;

/**
 * @method void onLanguageChange(ActiveProfile $profile)
 * @method void onCountryChange(ActiveProfile $profile)
 * @method void onCurrencyChange(ActiveProfile $profile)
 */
final class ActiveProfileChangeNotifier
{
	use SmartObject;

	/** @var NULL|callable[] */
	public $onLanguageChange;

	/** @var NULL|callable[] */
	public $onCountryChange;

	/** @var NULL|callable[] */
	public $onCurrencyChange;

	/**
	 * @param callable $listener
	 *
	 * @return void
	 */
	public function addOnLanguageChangeListener(callable $listener): void
	{
		$this->onLanguageChange[] = $listener;
	}

	/**
	 * @param callable $listener
	 *
	 * @return void
	 */
	public function addOnCountryChangeListener(callable $listener): void
	{
		$this->onCountryChange[] = $listener;
	}

	/**
	 * @param callable $listener
	 *
	 * @return void
	 */
	public function addOnCurrencyChangeListener(callable $listener): void
	{
		$this->onCurrencyChange[] = $listener;
	}

	/**
	 * @internal
	 * @param \SixtyEightPublishers\i18n\Profile\ActiveProfile $profile
	 *
	 * @return void
	 */
	public function notifyOnLanguageChange(ActiveProfile $profile): void
	{
		$this->onLanguageChange($profile);
	}

	/**
	 * @internal
	 * @param \SixtyEightPublishers\i18n\Profile\ActiveProfile $profile
	 *
	 * @return void
	 */
	public function notifyOnCountryChange(ActiveProfile $profile): void
	{
		$this->onCountryChange($profile);
	}

	/**
	 * @internal
	 * @param \SixtyEightPublishers\i18n\Profile\ActiveProfile $profile
	 *
	 * @return void
	 */
	public function notifyOnCurrencyChange(ActiveProfile $profile): void
	{
		$this->onCurrencyChange($profile);
	}
}
