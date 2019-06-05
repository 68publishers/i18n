<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Helper;

use Kdyby;
use Nette;
use Tracy;
use Tester;
use SixtyEightPublishers;

final class ContainerFactory
{
	/**
	 * @param string       $name
	 * @param string|array $config
	 *
	 * @return \Nette\DI\Container
	 */
	public static function createContainer(string $name, $config): Nette\DI\Container
	{
		if (!defined('TEMP_PATH')) {
			define('TEMP_PATH', __DIR__ . '/../temp');
		}

		$loader = new Nette\DI\ContainerLoader(TEMP_PATH . '/cache/Nette.Configurator', TRUE);
		$class = $loader->load(function (Nette\DI\Compiler $compiler) use ($config): void {
			$compiler->addExtension('translation', new Kdyby\Translation\DI\TranslationExtension());
			$compiler->addExtension('tracy', new Tracy\Bridges\Nette\TracyExtension());
			$compiler->addExtension('http', new Nette\Bridges\HttpDI\HttpExtension());
			$compiler->addExtension('session', new Nette\Bridges\HttpDI\SessionExtension());
			$compiler->addExtension('i18n', new SixtyEightPublishers\i18n\DI\I18nExtension());

			$compiler->addConfig([
				'parameters' => [
					'appDir' => __DIR__ . '/../../src',
					'tempDir' => __DIR__ . '/../temp',
					'wwwDir' => __DIR__ . '/../temp/temp',
					'filesDir' => __DIR__ . '/../files',
					'debugMode' => FALSE,
				],
			]);

			if (is_array($config)) {
				$compiler->addConfig($config);
			} elseif (is_string($config) && is_file($config)) {
				$compiler->loadConfig($config);
			} elseif (NULL !== $config) {
				$compiler->loadConfig(Tester\FileMock::create((string) $config, 'neon'));
			}
		}, $name);

		return new $class();
	}
}
