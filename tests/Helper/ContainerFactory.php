<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Helper;

use Nette\Configurator;
use Nette\DI\Container;

final class ContainerFactory
{
	/**
	 * @param string|array|NULL $config
	 *
	 * @return \Nette\DI\Container
	 */
	public static function createContainer($config = NULL): Container
	{
		$configurator = new Configurator();

		$configurator->setTempDirectory(TEMP_PATH);

		if (NULL !== $config) {
			$configurator->addConfig($config);
		}

		return $configurator->createContainer();
	}
}
