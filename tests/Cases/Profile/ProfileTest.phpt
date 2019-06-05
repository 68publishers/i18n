<?php

declare(strict_types=1);

namespace SixtyEightPublishers\i18n\Tests\Cases\Profile;

use Tester;
use SixtyEightPublishers;

require __DIR__ . '/../../bootstrap.php';

final class ProfileTest extends Tester\TestCase
{
	/**
	 * @return void
	 */
	public function testProfileGetters(): void
	{
		$profile = new SixtyEightPublishers\i18n\Profile\Profile(
			'foo',
			[ 'cs_CZ', 'en_US' ],
			[ 'CZ', 'GB' ],
			[ 'CZK', 'GBP' ],
			[ 'example\.com\/foo\/' ],
			FALSE
		);

		Tester\Assert::equal('foo', $profile->getName());
		Tester\Assert::equal([ 'cs_CZ', 'en_US' ], $profile->getLanguages());
		Tester\Assert::equal([ 'CZ', 'GB' ], $profile->getCountries());
		Tester\Assert::equal([ 'CZK', 'GBP' ], $profile->getCurrencies());
		Tester\Assert::equal([ 'example\.com\/foo\/' ], $profile->getDomains());
		Tester\Assert::equal(FALSE, $profile->isEnabled());
	}
}

(new ProfileTest())->run();
