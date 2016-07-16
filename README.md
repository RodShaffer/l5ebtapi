# :package_name

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Laravel 5.2 eBay Trading API Package. Easily add the eBay Trading API capabilities to your Laravel 5.2 projects.

Supports PSR4 Auto loading

## Install

Via Composer

``` bash
$ composer require l5ebtapi/l5ebtapi
```

## Usage

``` php
$l5ebtapi_service = new l5ebtapi\L5ebtapi();
echo $l5ebtapi_service->echoPhrase('Hello, League!');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [:author_name][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/:vendor/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/:vendor/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/:vendor/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/:vendor/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/:vendor/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/l5ebtapi/l5ebtapi
[link-travis]: https://travis-ci.org/l5ebtapi/l5ebtapi
[link-scrutinizer]: https://scrutinizer-ci.com/g/l5ebtapi/l5ebtapi/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/l5ebtapi/l5ebtapi
[link-downloads]: https://packagist.org/packages/l5ebtapi/l5ebtapi
[link-author]: https://github.com/l5ebtapi
[link-contributors]: ../../contributors
