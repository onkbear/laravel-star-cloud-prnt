# Star CloudPRNT for Laravel

This package allows you to add [Star CloudPRNT](https://www.star-m.jp/products/s_print/CloudPRNTSDK/Documentation/en/) integration in your Laravel application.

## Installation

```
composer require onkbear/laravel-star-cloud-prnt
```

Please publish the config file with:

```
php artisan vendor:publish --provider="Onkbear\StarCloudPRNT\StarCloudPRNTServiceProvider"
```

This is the contents of the config file that will be published at `config/star-cloud-prnt.php`:

Please add CloudPRNT server route to the routes file. It is used in 

``` php
Route::starCloudPRNT('cloud-prnt-route');
```

You will need to add that route to the except array of the `VerifyCsrfToken` middleware:

``` php
protected $except = [
    'cloud-prnt-route',
];
```

## Buffer API

### Print Mode

| Name | Method |
|--|--|
| Select emphasized printing | setTextEmphasized |

### Horizontal Direction Printing Position

| Specify left alignment | setTextLeftAlign |
| Specify center alignment | setTextCenterAlign |
| Specify right alignment | setTextRightAlign |

### Font style and character set

| Select code page | setCodepage |

## Star CloudPRNT client configuration

Please look at the [Online Manual](https://www.star-m.jp/products/s_print/mcprint3/manual/en/settings/settingsCloudPRNT.htm)

## HTTP Basic Auth

If you need to integrate with basic auth, following package allows you to add it very easily without using DB.

- [olssonm/l5-very-basic-auth](https://github.com/olssonm/l5-very-basic-auth)
