<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Nette;
use Tester;
use Mockery;
use SixtyEightPublishers;

require __DIR__ . '/../../bootstrap.php';

final class NetteRequestDetectorTest extends Tester\TestCase
{
	/** @var \SixtyEightPublishers\i18n\Profile\IProfile */
	private $fooProfile;

	/** @var \SixtyEightPublishers\i18n\Profile\IProfile */
	private $barProfile;

	/** @var \SixtyEightPublishers\i18n\Profile\IProfile */
	private $bazProfile;

	/** @var \SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer */
	private $profileContainer;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		$this->fooProfile = $foo = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);
		$this->barProfile = $bar = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);
		$this->bazProfile = $baz = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);
		$this->profileContainer = $container = Mockery::mock(SixtyEightPublishers\i18n\ProfileContainer\IProfileContainer::class);

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

		Tester\Assert::same($this->fooProfile, $detector->detect($this->profileContainer));
	}

	/**
	 * @return void
	 */
	public function testDetectByFullName(): void
	{
		$detector = $this->createDetector('example.com/bar');

		Tester\Assert::same($this->barProfile, $detector->detect($this->profileContainer));
	}

	/**
	 * @return void
	 */
	public function testDetectWithMultipleDomains(): void
	{
		$detector = $this->createDetector('bar.example.com');

		Tester\Assert::same($this->barProfile, $detector->detect($this->profileContainer));
	}

	/**
	 * @return void
	 */
	public function testDontDetectDisabledProfile(): void
	{
		$detector = $this->createDetector('www.example.com/baz');

		Tester\Assert::equal(NULL, $detector->detect($this->profileContainer));
	}

	/**
	 * @param string $url
	 *
	 * @return \SixtyEightPublishers\i18n\Detector\NetteRequestDetector
	 */
	private function createDetector(string $url): SixtyEightPublishers\i18n\Detector\NetteRequestDetector
	{
		return new SixtyEightPublishers\i18n\Detector\NetteRequestDetector(
			$this->createRequest($url)
		);
	}

	/**
	 * @param string $url
	 *
	 * @return \Nette\Http\IRequest
	 */
	private function createRequest(string $url): Nette\Http\IRequest
	{
		$urlScript = Mockery::mock(Nette\Http\UrlScript::class);
		$request = Mockery::mock(Nette\Http\IRequest::class);

		$urlScript->shouldReceive('getAbsoluteUrl')->once()->andReturn($url);
		$request->shouldReceive('getUrl')->once()->andReturn($urlScript);

		return $request;
	}
}

(new NetteRequestDetectorTest())->run();
