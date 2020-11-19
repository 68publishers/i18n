<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\ProfileContainer;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\i18n\Profile\ProfileInterface;
use SixtyEightPublishers\i18n\ProfileContainer\ProfileContainer;
use SixtyEightPublishers\i18n\Exception\InvalidArgumentException;

require __DIR__ . '/../../bootstrap.php';

final class ProfileContainerTest extends TestCase
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
			$this->profiles[$name] = $profile = Mockery::mock(ProfileInterface::class);
			$profile->shouldReceive('getName')->andReturn($name);
		}

		$profiles = $this->profiles;
		$default = $profiles['default'];
		unset($profiles['default']);

		$this->profileContainer = new ProfileContainer($default, array_values($profiles));
	}

	/**
	 * @return void
	 */
	public function testThrowExceptionOnMissingProfile(): void
	{
		$defaultProfile = Mockery::mock(ProfileInterface::class);
		$defaultProfile->shouldReceive('getName')->andReturn('default');

		$profileContainer = new ProfileContainer($defaultProfile, []);

		Assert::exception(
			function () use ($profileContainer) {
				$profileContainer->get('foo');
			},
			InvalidArgumentException::class,
			'Profile with name "foo" is not defined.'
		);
	}

	/**
	 * @return void
	 */
	public function testGetProfileByName(): void
	{
		Assert::equal($this->profiles['foo'], $this->profileContainer->get('foo'));
	}

	/**
	 * @return void
	 */
	public function testGetDefaultProfile(): void
	{
		Assert::equal($this->profiles['default'], $this->profileContainer->get());
	}

	/**
	 * @return void
	 */
	public function testToArrayMethod(): void
	{
		Assert::equal($this->profiles, $this->profileContainer->toArray());
	}

	/**
	 * @return void
	 */
	public function testIterator(): void
	{
		Assert::type(\Traversable::class, $this->profileContainer->getIterator());
	}
}

(new ProfileContainerTest())->run();
