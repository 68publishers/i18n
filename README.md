> :warning: Warning! This package does not have active support, it exists only for the historical needs of the author.

# i18n

[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![Latest Version on Packagist][ico-version]][link-packagist]

This package helps you to deal with regions with different languages, currencies and countries. It could be helpful even if you have single region project.

## Installation

The best way to install 68publishers/i18n is using Composer:

```bash
$ composer require 68publishers/i18n
```

then you can register extension into DIC:

```neon
extensions:
    68publishers.i18n: SixtyEightPublishers\i18n\DI\I18nExtension(%debugMode%)
```

## Configuration

```neon
68publishers.i18n:
    profiles:
        europe:
            language: [ sk_SK, en_GB, de_DE, pl_PL ]
            currency: [ EUR, PLZ, GBP ]
            country: [ SK, GB, DE, PL ]
            domain: [ 'europe\.example\.com' ] # regex
        north_america:
            language: en_US
            currency: USD
            country: US
            domain: 'example\.com\/na'
            enabled: no # default is `yes`
        default: # If the default profile doesn't exists, the first profile is taken as default
            language: cs_CZ
            currency: CZK
            country: CZ
    lists:
        fallback_language: en # default
        default_language: null # default

    translation_bridge:
        locale_resolver:
            enabled: yes # registers custom TranslatorLocaleResolver through 68publishers/translation-bridge extension
            use_default: yes # use language of default's profile if profile is not detected, default is `no`
            priority: 15

    # if you want to use custom profile storage or profile detector:
    storage: My\Custom\ProfileStorage
    detector: My\Custom\Detector
```

### Integration with 68publishers/translation-bridge

#### Translator Locale Resolving

The Translator's locale can be resolved by the currently active profile.
That is done with custom `TranslatorLocaleResolver` that is automatically registered if an option `translation_bridge.locale_resolver.enabled` is set to `TRUE`.

#### Synchronization Between Profile's Language and Translator's Locale

The Translator's locale is automatically changed when the active profile's language is changed.

```php
<?php

/** @var \SixtyEightPublishers\i18n\Profile\ActiveProfile $activeProfile */
/** @var \SixtyEightPublishers\TranslationBridge\Localization\TranslatorLocalizerInterface $localizer */

$activeProfile->changeLanguage('en_GB');
$localizer->getLocale(); # en_GB

$activeProfile->changeLanguage('cs_CZ');
$localizer->getLocale(); # cs_CZ
```

## Contributing

Before committing any changes, don't forget to run

```bash
$ vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run
```

and

```bash
$ composer run tests
```

[ico-version]: https://img.shields.io/packagist/v/68publishers/i18n.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/68publishers/i18n/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/68publishers/i18n.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/68publishers/i18n.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/68publishers/i18n.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/68publishers/i18n
[link-travis]: https://travis-ci.com/68publishers/i18n
[link-scrutinizer]: https://scrutinizer-ci.com/g/68publishers/i18n/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/68publishers/i18n
[link-downloads]: https://packagist.org/packages/68publishers/i18n
