<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\ProfileContainer;

use Tester;
use Mockery;
use SixtyEightPublishers;

require __DIR__ . '/../../bootstrap.php';

final class ProfileContainerTest extends Tester\TestCase
{
	/** @var array  */
	private $profiles = [];

	/** @var NULL|\SixtyEightPublishers\i18n\ProfileContainer\ProfileContainer */
	private $profileContainer;

	/**
	 * {@inheritdoc}
	 */
	protected function tearDown(): void
	{
		parent::tearDown();

		Mockery::close();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		foreach ([ 'default', 'foo', 'bar', 'baz' ] as $name) {
			$this->profiles[$name] = $profile = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);
			$profile->shouldReceive('getName')->andReturn($name);
		}

		$profiles = $this->profiles;
		$default = $profiles['default'];
		unset($profiles['default']);

		$this->profileContainer = new SixtyEightPublishers\i18n\ProfileContainer\ProfileContainer($default, array_values($profiles));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingProfile(): void
	{
		$defaultProfile = Mockery::mock(SixtyEightPublishers\i18n\Profile\IProfile::class);
		$defaultProfile->shouldReceive('getName')->andReturn('default');

		$profileContainer = new SixtyEightPublishers\i18n\ProfileContainer\ProfileContainer($defaultProfile, []);

		Tester\Assert::exception(
			function () use ($profileContainer) {
				$profileContainer->get('foo');
			},
			SixtyEightPublishers\i18n\Exception\InvalidArgumentException::class,
			'Profile with name "foo" is not defined.'
		);
	}

	/**
	 * @return void
	 */
	public function testGetProfileByName(): void
	{
		Tester\Assert::equal($this->profiles['foo'], $this->profileContainer->get('foo'));
	}

	/**
	 * @return void
	 */
	public function testGetDefaultProfile(): void
	{
		Tester\Assert::equal($this->profiles['default'], $this->profileContainer->get());
	}

	/**
	 * @return void
	 */
	public function testToArrayMethod(): void
	{
		Tester\Assert::equal($this->profiles, $this->profileContainer->toArray());
	}

	/**
	 * @return void
	 */
	public function testIterator(): void
	{
		Tester\Assert::type(\Traversable::class, $this->profileContainer->getIterator());
	}
}

(new ProfileContainerTest())->run();
