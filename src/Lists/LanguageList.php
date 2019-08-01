<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Lists;

final class LanguageList extends AbstractList
{
	/**
	 * {@inheritdoc}
	 */
	protected function getSourcePathMask(): string
	{
		return '%s/umpirsky/locale-list/data/%s/locales.php';
	}
}
