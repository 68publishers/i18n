<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Translator;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Nette\Localization\ITranslator;
use SixtyEightPublishers\i18n\Profile\Profile;
use SixtyEightPublishers\i18n\Profile\ActiveProfile;
use SixtyEightPublishers\i18n\ProfileProviderInterface;
use SixtyEightPublishers\i18n\Storage\ProfileStorageInterface;
use SixtyEightPublishers\i18n\Profile\ActiveProfileChangeNotifier;
use SixtyEightPublishers\i18n\Translation\TranslatorLocaleResolver;

require __DIR__ . '/../../bootstrap.php';

final class TranslatorLocaleResolverTest extends TestCase
{
	/** @var \SixtyEightPublishers\i18n\ProfileProviderInterface|NULL */
	private $profileProvider;

	/** @var \Nette\Localization\ITranslator|NULL */
	private $translator;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$activeProfile = new ActiveProfile(
			new Profile('foo', ['cs_CZ'], ['CZ'], ['CZK'], [], TRUE),
			new ActiveProfileChangeNotifier(),
			Mockery::mock(ProfileStorageInterface::class)
		);

		$this->profileProvider = Mockery::mock(ProfileProviderInterface::class);
		$this->translator = Mockery::mock(ITranslator::class);

		$this->profileProvider->shouldReceive('getProfile')->andReturn($activeProfile);
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
	public function testLocaleShouldBeResolverWhenUseDefault(): void
	{
		$translatorLocaleResolver = new TranslatorLocaleResolver($this->profileProvider, TRUE);

		Assert::same('cs_CZ', $translatorLocaleResolver->resolveLocale($this->translator));
	}

	/**
	 * @return void
	 */
	public function testLocaleShouldNotBeResolverWhenDefaultIsNotUsed(): void
	{
		$translatorLocaleResolver = new TranslatorLocaleResolver($this->profileProvider, FALSE);

		Assert::null($translatorLocaleResolver->resolveLocale($this->translator));
	}
}

(new TranslatorLocaleResolverTest())->run();
