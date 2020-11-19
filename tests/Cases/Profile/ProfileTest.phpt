<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\i18n\Profile\Profile;

require __DIR__ . '/../../bootstrap.php';

final class ProfileTest extends TestCase
{
	/**
	 * @return void
	 */
	public function testProfileGetters(): void
	{
		$profile = new Profile(
			'foo',
			[ 'cs_CZ', 'en_US' ],
			[ 'CZ', 'GB' ],
			[ 'CZK', 'GBP' ],
			[ 'example\.com\/foo\/' ],
			FALSE
		);

		Assert::equal('foo', $profile->getName());
		Assert::equal([ 'cs_CZ', 'en_US' ], $profile->getLanguages());
		Assert::equal([ 'CZ', 'GB' ], $profile->getCountries());
		Assert::equal([ 'CZK', 'GBP' ], $profile->getCurrencies());
		Assert::equal([ 'example\.com\/foo\/' ], $profile->getDomains());
		Assert::equal(FALSE, $profile->isEnabled());
	}
}

(new ProfileTest())->run();
