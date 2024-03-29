<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Diagnostics;

use Tracy\IBarPanel;
use Nette\Utils\Html;
use Nette\SmartObject;
use SixtyEightPublishers\i18n\ProfileProviderInterface;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

final class Panel implements IBarPanel
{
	use SmartObject;

	/** @var \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface  */
	private $profileContainer;

	/** @var \SixtyEightPublishers\i18n\ProfileProviderInterface  */
	private $profileProvider;

	/**
	 * @param \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface $profileContainer
	 * @param \SixtyEightPublishers\i18n\ProfileProviderInterface                   $profileProvider
	 */
	public function __construct(ProfileContainerInterface $profileContainer, ProfileProviderInterface $profileProvider)
	{
		$this->profileContainer = $profileContainer;
		$this->profileProvider = $profileProvider;
	}

	/**
	 * @return string
	 */
	public function getTab(): string
	{
		return (string) Html::el('span title="i18n"')
			->addHtml($this->getIcon())
			->addHtml(
				Html::el('span class=tracy-label')->setText($this->profileProvider->getProfile()->getName())
			);
	}

	/**
	 * @return string
	 */
	public function getPanel(): string
	{
		$panel = [];
		$panel[] = Html::el('h2')->setText('Configured profiles:');

		$table = Html::el('table');
		$table->addHtml(Html::el('thead')->addHtml(
			Html::el('tr')
				->addHtml(Html::el('th'))
				->addHtml(Html::el('th')->setText('name'))
				->addHtml(Html::el('th')->setText('country'))
				->addHtml(Html::el('th')->setText('language'))
				->addHtml(Html::el('th')->setText('currency'))
				->addHtml(Html::el('th')->setText('domain'))
		));
		$table->addHtml($tbody = Html::el('tbody'));

		foreach ($this->profileContainer->toArray() as $profile) {
			$tbody->addHtml(
				$tr = Html::el('tr')
				->addHtml($firstCell = Html::el('td'))
				->addHtml(Html::el('td')->setText($profile->getName()))
				->addHtml(Html::el('td')->setHtml(implode('<br>', $profile->getCountries())))
				->addHtml(Html::el('td')->setHtml(implode('<br>', $profile->getLanguages())))
				->addHtml(Html::el('td')->setHtml(implode('<br>', $profile->getCurrencies())))
				->addHtml(Html::el('td')->setHtml(implode('<br>', $profile->getDomains())))
			);

			if ($profile->getName() === $this->profileProvider->getProfile()->getName()) {
				/** @noinspection PhpUndefinedFieldInspection */
				$tr->class[] = 'yes';
				$firstCell->setText('✓');
			} elseif (!$profile->isEnabled()) {
				/** @noinspection PhpUndefinedFieldInspection */
				$tr->class[] = 'disabled';
				$firstCell->setText('✗');
			}
		}

		$panel[] = $table;
		$h1 = Html::el('h1')->setText('i18n');

		return $h1 . Html::el('div class="nette-inner tracy-inner sixtyEightPublishers-EnvironmentPanel"')->setHtml(implode(' ', $panel)) . $this->getStyles();
	}

	/**
	 * @return string
	 */
	private function getIcon(): string
	{
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="2 2 28 28">
			<path fill="#007CFF" d="M16 4c-6.627 0-12 5.373-12 12s5.373 12 12 12c0.811 0 1.603-0.081 2.369-0.234-0.309-0.148-0.342-1.255-0.037-1.887 0.34-0.703 1.406-2.484 0.352-3.082s-0.762-0.867-1.406-1.559-0.381-0.795-0.422-0.973c-0.141-0.609 0.621-1.523 0.656-1.617s0.035-0.445 0.023-0.551-0.48-0.387-0.598-0.398-0.176 0.188-0.34 0.199-0.879-0.434-1.031-0.551-0.223-0.398-0.434-0.609-0.234-0.047-0.563-0.176-1.383-0.516-2.191-0.844-0.879-0.788-0.891-1.113-0.492-0.797-0.718-1.137c-0.225-0.34-0.267-0.809-0.349-0.703s0.422 1.336 0.34 1.371-0.258-0.34-0.492-0.645 0.246-0.141-0.504-1.617 0.234-2.229 0.281-3 0.633 0.281 0.328-0.211 0.023-1.523-0.211-1.898-1.57 0.422-1.57 0.422c0.035-0.363 1.172-0.984 1.992-1.559s1.321-0.129 1.98 0.082 0.703 0.141 0.48-0.070 0.094-0.316 0.609-0.234 0.656 0.703 1.441 0.645 0.082 0.152 0.188 0.352-0.117 0.176-0.633 0.527 0.012 0.352 0.926 1.020 0.633-0.445 0.539-0.938 0.668-0.105 0.668-0.105c0.563 0.375 0.459 0.021 0.869 0.15s1.522 1.069 1.522 1.069c-1.395 0.762-0.516 0.844-0.281 1.020s-0.48 0.516-0.48 0.516c-0.293-0.293-0.34 0.012-0.527 0.117s-0.012 0.375-0.012 0.375c-0.97 0.152-0.75 1.172-0.738 1.418s-0.621 0.621-0.785 0.973 0.422 1.113 0.117 1.16-0.609-1.148-2.25-0.703c-0.495 0.134-1.594 0.703-1.008 1.863s1.559-0.328 1.887-0.164-0.094 0.902-0.023 0.914 0.926 0.032 0.973 1.031 1.301 0.914 1.57 0.938 1.172-0.738 1.301-0.773 0.645-0.469 1.77 0.176 1.699 0.551 2.086 0.82 0.117 0.809 0.48 0.984 1.816-0.059 2.18 0.539-1.5 3.598-2.086 3.926-0.855 1.078-1.441 1.559-1.406 1.075-2.18 1.535c-0.685 0.407-0.808 1.136-1.113 1.367 5.37-1.193 9.386-5.985 9.386-11.714 0-6.627-5.373-12-12-12zM18.813 15.262c-0.164 0.047-0.504 0.352-1.336-0.141s-1.406-0.398-1.477-0.48c0 0-0.070-0.199 0.293-0.234 0.746-0.072 1.688 0.691 1.898 0.703s0.316-0.211 0.691-0.090c0.375 0.121 0.094 0.196-0.070 0.242zM14.887 5.195c-0.082-0.059 0.068-0.128 0.157-0.246 0.051-0.068 0.013-0.182 0.078-0.246 0.176-0.176 1.043-0.422 0.873 0.059s-0.979 0.527-1.108 0.434zM16.984 6.719c-0.293-0.012-0.983-0.085-0.855-0.211 0.495-0.492-0.188-0.633-0.609-0.668s-0.598-0.27-0.387-0.293 1.055 0.012 1.195 0.129 0.902 0.422 0.949 0.645 0 0.41-0.293 0.398zM19.527 6.637c-0.234 0.188-1.414-0.673-1.641-0.867-0.984-0.844-1.512-0.563-1.718-0.703s-0.133-0.328 0.183-0.609 1.207 0.094 1.723 0.152 1.113 0.457 1.125 0.931c0.012 0.474 0.563 0.909 0.328 1.097z"></path>
		</svg>';
	}

	/**
	 * @return string
	 */
	private function getStyles(): string
	{
		return "<style>
			#nette-debug .sixtyEightPublishers-EnvironmentPanel h2, #tracy-debug .sixtyEightPublishers-EnvironmentPanel h2 {
				font-size: 14px;
			}
			#nette-debug .sixtyEightPublishers-EnvironmentPanel tr.yes td, #tracy-debug .sixtyEightPublishers-EnvironmentPanel tr.yes td {
				background: #BDE678;
			}
			#nette-debug .sixtyEightPublishers-EnvironmentPanel tr.disabled td, #tracy-debug .sixtyEightPublishers-EnvironmentPanel tr.disabled td {
				background: #DDD;
				color: #888;
			}
		</style>";
	}
}
