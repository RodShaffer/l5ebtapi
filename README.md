# l5ebtapi

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

Laravel 5 eBay Trading API Package. Easily add the eBay Trading API to your Laravel 5 projects.

#####I apologize for the inactivity on the L5ebtapi package but I have had some unforeseen family issues come up and I no longer have time to invest into the L5ebtapi package. If anyone has interest in taking over the project feel free to clone the project. The project was developed using PhpStorm 2017

This package is under development. All methods currently written have been tested as much as possible.

While using this library if you come accross any issues please report them to me(Rod) at the email address below. Try to
provide as much information about the issue as possible. Thank you in advance.

I will be updating the documentation as much as possible but I do have a full time job and between development of this
package and the custom software I am developing the documentation is kind of lacking sometimes. Any help would be
greatly appreciated.

Supports PSR4 Auto loading

## Install

Via Composer

``` bash
$ composer require rodshaffer/l5ebtapi
```

Then Add the following to the Application Service Providers array in your config/app.php file

rodshaffer\l5ebtapi\L5ebtapiServiceProvider::class,

l5ebtapi requires a configuration file (to hold your app's eBay developer account credentials).
Just execute Laravel's vendor:publish command and l5ebtapi.php(the default config file) will be published to the config
folder. Make sure you edit and save this file with your app's eBay developer credentials.

## Usage

For information on usage visit our [Wiki]

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email htmlplus43@yahoo.com instead of using the issue tracker.

## Credits

- [Rod Shaffer][https://github.com/rodshaffer/l5ebtapi]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v1.3.7/:rodshaffer/:l5ebtapi.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/l5ebtapi/l5ebtapi.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/rodshaffer/l5ebtapi
[link-downloads]: https://packagist.org/packages/rodshaffer/l5ebtapi
[Wiki]: https://github.com/rodshaffer/l5ebtapi/wiki
[link-author]: https://github.com/rodshaffer/l5ebtapi
