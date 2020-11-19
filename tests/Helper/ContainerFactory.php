<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Helper;

use Tester\FileMock;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\Bridges\HttpDI\HttpExtension;
use Tracy\Bridges\Nette\TracyExtension;
use Nette\Bridges\HttpDI\SessionExtension;
use Kdyby\Translation\DI\TranslationExtension;
use SixtyEightPublishers\i18n\DI\I18nExtension;

final class ContainerFactory
{
	/**
	 * @param string       $name
	 * @param string|array $config
	 *
	 * @return \Nette\DI\Container
	 */
	public static function createContainer(string $name, $config): Container
	{
		if (!defined('TEMP_PATH')) {
			define('TEMP_PATH', __DIR__ . '/../temp');
		}

		$loader = new ContainerLoader(TEMP_PATH . '/cache/Nette.Configurator', TRUE);
		$class = $loader->load(static function (Compiler $compiler) use ($config): void {
			$compiler->addExtension('translation', new TranslationExtension());
			$compiler->addExtension('tracy', new TracyExtension());
			$compiler->addExtension('http', new HttpExtension());
			$compiler->addExtension('session', new SessionExtension());
			$compiler->addExtension('i18n', new I18nExtension());

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
				$compiler->loadConfig(FileMock::create((string) $config, 'neon'));
			}
		}, $name);

		return new $class();
	}
}
