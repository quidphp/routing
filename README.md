# QuidPHP/Routing
[![Release](https://img.shields.io/github/v/release/quidphp/routing)](https://packagist.org/packages/quidphp/routing)
[![License](https://img.shields.io/github/license/quidphp/routing)](https://github.com/quidphp/routing/blob/master/LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/quidphp/routing)](https://www.php.net)
[![Style CI](https://styleci.io/repos/203673693/shield)](https://styleci.io)
[![Code Size](https://img.shields.io/github/languages/code-size/quidphp/routing)](https://github.com/quidphp/routing)

## About
**QuidPHP/Routing** is a PHP library that provides a simple route matching and triggering procedure. It is part of the [QuidPHP](https://github.com/quidphp/project) package and can also be used standalone. 

## License
**QuidPHP/Routing** is available as an open-source software under the [MIT license](LICENSE).

## Installation
**QuidPHP/Routing** can be easily installed with [Composer](https://getcomposer.org). It is available on [Packagist](https://packagist.org/packages/quidphp/routing).
``` bash
$ composer require quidphp/routing
```
Once installed, the **Quid\Routing** namespace will be available within your PHP application.

## Requirement
**QuidPHP/Routing** requires the following:
- PHP 7.2

## Dependency
**QuidPHP/Routing** has the following dependency:
- [Quid\Base](https://github.com/quidphp/base)
- [Quid\Main](https://github.com/quidphp/main)

## Testing
**QuidPHP/Routing** testsuite can be run by creating a new [Quid\Project](https://github.com/quidphp/project). All tests and assertions are part of the [Quid\Test](https://github.com/quidphp/test) repository.

## Comment
**QuidPHP/Routing** code is commented and all methods are explained. However, the method and property comments are currently written in French.

## Convention
**QuidPHP/Routing** is built on the following conventions:
- *Traits*: Traits filenames start with an underscore (_).
- *Coding*: No curly braces are used in a IF statement if the condition can be resolved in only one statement.
- *Type*: Files, function arguments and return types are strict typed.
- *Config*: A special $config static property exists in all classes. This property gets recursively merged with the parents' property on initialization.
- *M-VC*: A route object serves as both a View and a Controller. There should be one route class per route. 
- *Segment*: A segment within a route path represents a dynamic value. It is wrapped around brackets. A callable that accepts or rejects the value need to be provided.
- *Templating*: Once the route is triggered, the developer has complete control and can use any HTML rendering technology it desires. Or, simply use the [Quid\Base\Html](https://github.com/quidphp/base/blob/master/src/Html.php) methods to generate the HTML and use traits for reusable page components.

## Overview
**QuidPHP/Routing** contains 6 classes and traits. Here is an overview:
- [Exception](src/Exception.php) | Class used for a catchable route exception, the next available route will instead be triggered
- [Route](src/Route.php) | Abstract class for a route that acts as both a View and a Controller
- [RouteRequest](src/RouteRequest.php) | Class that analyzes if a request matches a route
- [RouteSegmentRequest](src/RouteSegmentRequest.php) | Class that analyzes if a request matches a route with segment (non-static value)
- [Routes](src/Routes.php) | Class for a collection of many untriggered routes
- [_segment](src/_segment.php) | Trait that provides logic for a route with segment (non-static value)