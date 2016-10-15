# l5ebtapi

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Laravel 5 eBay Trading API Package. Easily add the eBay Trading API capabilities to your Laravel 5 projects.

#####This package is under development and will be changing frequently. Use at your own risk.

I will be updating the documentation as much as possible but I do have a full time job and between development of this
package and the custom software I am developing the documentation is kind of lacking sometimes.

Supports PSR4 Auto loading

## Install

Via Composer

``` bash
$ composer require rodshaffer/l5ebtapi
```

Then Add the following to the Application Service Providers array in your config/app.php file

rodshaffer\l5ebtapi\L5ebtapiServiceProvider::class,

## Usage

For information on usage visit our [Wiki]
I will be updating the documentation in the near future and I will also be changing the location that the documentation
 is stored. This will hopefully make the readability better. So please stay tuned for the changes that are coming.
 The addfixedpriceitem method now supports all of the features. If you find any bugs please send me an email at the
 email address below.
 I also fixed the uploadsitehostedpictures method to support all the features but discovered a slight glitch if you want
 to use an external url for your pictures so I will be fixing this in the near future. However other then this small bug
 the method fully works.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email htmlplus43@@yahoo.com instead of using the issue tracker.

## Credits

- [Rod Shaffer][https://github.com/rodshaffer/l5ebtapi]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/:vendor/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/l5ebtapi/l5ebtapi.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/rodshaffer/l5ebtapi
[link-downloads]: https://packagist.org/packages/rodshaffer/l5ebtapi
[Wiki]: https://github.com/rodshaffer/l5ebtapi/wiki
[link-author]: https://github.com/rodshaffer/l5ebtapi
