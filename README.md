# i18n

This package helps you to deal with regions with different languages, currencies and countries. It could be helpful even if you have single region project.

## Installation

The best way to install 68publishers/i18n is using Composer:

```bash
composer require 68publishers/i18n
```

then you can register extension into DIC:

```yaml
extensions:
    i18n: SixtyEightPublishers\i18n\DI\I18nExtension
```

## Configuration

```yaml
environment:
    profile:
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
			
	debugger: yes # adds Tracy panel, default is parameter %debugMode%
	translations:
		enabled: yes # enable integration with kdyby/translation, default is `no`
		use_default: yes # use language of default's profile if profile is not detected, default is `no`
	
	# if you want to use custom profile storage or profile detector:
	storage: My\Custom\ProfileStorage
	detector: My\Custom\Detector
```

### Integration with Kdyby\Translation

This feature provides automatic evaluation of the locale parameter for `kdyby\translation` based on profile settings in the extension. 
Default profile's language can be used if setting `translations.use_default` is set to `TRUE`.
If is this setting set to `FALSE` default language will not be used and other resolvers will be invoked.
Also if you change language via method `SixtyEightPublishers\i18n\Profile\ActiveProfile::changeLanguage()`, locale in Translator will be changed too.

## Contributing

Before committing any changes, don't forget to run

```bash
vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run
```

and

```bash
vendor/bin/tester ./tests
```
