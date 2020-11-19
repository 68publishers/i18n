<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\Detector\NetteRequestDetector;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface;

require __DIR__ . '/../../bootstrap.php';

final class NetteRequestDetectorTest extends TestCase
{
	/** @var \SixtyEightPublishers\i18n\Profile\ProfileInterface */
	private $fooProfile;

	/** @var \SixtyEightPublishers\i18n\Profile\ProfileInterface */
	private $barProfile;

	/** @var \SixtyEightPublishers\i18n\Profile\ProfileInterface */
	private $bazProfile;

	/** @var \SixtyEightPublishers\i18n\ProfileContainer\ProfileContainerInterface */
	private $profileContainer;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		$this->fooProfile = $foo = Mockery::mock(ProfileInterface::class);
		$this->barProfile = $bar = Mockery::mock(ProfileInterface::class);
		$this->bazProfile = $baz = Mockery::mock(ProfileInterface::class);
		$this->profileContainer = $container = Mockery::mock(ProfileContainerInterface::class);

		$foo->shouldReceive('getDomains')->andReturn([ 'example\.com\/foo' ]);
		$foo->shouldReceive('isEnabled')->andReturn(TRUE);

		$bar->shouldReceive('getDomains')->andReturn([ 'example.com/bar', 'bar\.example\.com' ]);
		$bar->shouldReceive('isEnabled')->andReturn(TRUE);

		$baz->shouldReceive('getDomains')->andReturn([ 'example\.com\/baz' ]);
		$baz->shouldReceive('isEnabled')->andReturn(FALSE);

		$container->shouldReceive('toArray')->andReturn([ $foo, $bar, $baz ]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	/**
	 * @return void
	 */
	public function testDetectByRegex(): void
	{
		$detector = $this->createDetector('www.example.com/foo');

		Assert::same($this->fooProfile, $detector->detect($this->profileContainer));
	}

	/**
	 * @return void
	 */
	public function testDetectByFullName(): void
	{
		$detector = $this->createDetector('example.com/bar');

		Assert::same($this->barProfile, $detector->detect($this->profileContainer));
	}

	/**
	 * @return void
	 */
	public function testDetectWithMultipleDomains(): void
	{
		$detector = $this->createDetector('bar.example.com');

		Assert::same($this->barProfile, $detector->detect($this->profileContainer));
	}

	/**
	 * @return void
	 */
	public function testDontDetectDisabledProfile(): void
	{
		$detector = $this->createDetector('www.example.com/baz');

		Assert::equal(NULL, $detector->detect($this->profileContainer));
	}

	/**
	 * @param string $url
	 *
	 * @return \SixtyEightPublishers\i18n\Detector\NetteRequestDetector
	 */
	private function createDetector(string $url): NetteRequestDetector
	{
		return new NetteRequestDetector(
			$this->createRequest($url)
		);
	}

	/**
	 * @param string $url
	 *
	 * @return \Nette\Http\IRequest
	 */
	private function createRequest(string $url): IRequest
	{
		$urlScript = Mockery::mock(UrlScript::class);
		$request = Mockery::mock(IRequest::class);

		$urlScript->shouldReceive('getAbsoluteUrl')->once()->andReturn($url);
		$request->shouldReceive('getUrl')->once()->andReturn($urlScript);

		return $request;
	}
}

(new NetteRequestDetectorTest())->run();
