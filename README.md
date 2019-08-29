# Quid\Routing
[![Release](https://img.shields.io/github/v/release/quidphp/routing)](https://packagist.org/packages/quidphp/routing)
[![License](https://img.shields.io/github/license/quidphp/routing)](https://github.com/quidphp/routing/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/quidphp/routing)](https://www.php.net)
[![Style CI](https://styleci.io/repos/203673693/shield)](https://styleci.io)
[![Code Size](https://img.shields.io/github/languages/code-size/quidphp/routing)](https://github.com/quidphp/routing)

## About
**Quid\Routing** is a PHP library that provides a simple route matching and triggering procedure. It is part of the [QuidPHP](https://github.com/quidphp/project) package and can also be used standalone. 

## License
**Quid\Routing** is available as an open-source software under the [MIT license](LICENSE).

## Installation
**Quid\Routing** can be easily installed with [Composer](https://getcomposer.org). It is available on [Packagist](https://packagist.org/packages/quidphp/routing).
``` bash
$ composer require quidphp/routing
```

## Requirement
**Quid\Routing** requires the following:
- PHP 7.2

## Dependency
**Quid\Routing** has the following dependency:
- [Quid\Main](https://github.com/quidphp/main)
- [Quid\Base](https://github.com/quidphp/base)

## Testing
**Quid\Routing** testsuite can be run by creating a new [Quid\Project](https://github.com/quidphp/project). All tests and assertions are part of the [Quid\Test](https://github.com/quidphp/test) repository.

## Comment
**Quid\Routing** code is commented and all methods are explained. However, the method and property comments are currently written in French.

## Convention
**Quid\Routing** is built on the following conventions:
- *Traits*: Traits filenames start with an underscore (_).
- *Coding*: No curly braces are used in a IF statement if the condition can be resolved in only one statement.
- *Type*: Files, function arguments and return types are strict typed.
- *Config*: A special $config static property exists in all classes. This property gets recursively merged with the parents' property on initialization.

## Overview
**Quid\Routing** contains 6 classes and traits. Here is an overview:
- [Exception](src/Exception.php)
- [Route](src/Route.php)
- [RouteRequest](src/RouteRequest.php)
- [RouteSegmentRequest](src/RouteSegmentRequest.php)
- [Routes](src/Routes.php)
- [_segment](src/_segment.php)