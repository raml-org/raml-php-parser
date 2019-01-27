[![Build Status](https://api.travis-ci.org/raml-org/raml-php-parser.svg?branch=master)](https://www.travis-ci.org/raml-org/raml-php-parser)
[![Coverage Status](https://coveralls.io/repos/github/raml-org/raml-php-parser/badge.svg?branch=master)](https://coveralls.io/github/raml-org/raml-php-parser?branch=master)
[![Latest Stable Version](https://poser.pugx.org/raml-org/raml-php-parser/v/stable)](https://packagist.org/packages/raml-org/raml-php-parser)
[![Latest Unstable Version](https://poser.pugx.org/raml-org/raml-php-parser/v/unstable)](https://packagist.org/packages/raml-org/raml-php-parser)
[![Total Downloads](https://poser.pugx.org/raml-org/raml-php-parser/downloads)](https://packagist.org/packages/raml-org/raml-php-parser)

See the [RAML specification](https://github.com/raml-org/raml-spec).

## RAML 0.8 Support
For RAML 0.8 support follow version 2.

## RAML 1.0 Support
For RAML 1.0 support follow version 3 or above. RAML 1.0 support is still work in progress.

_What is done and should work:_
  - Part of RAML 1.0 [type expressions](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#type-expressions)
  - Enums
  - Union type expression (the "or" `|` operator)
  - Array of types
  - `discriminator` and `discriminatorValue` facets
  - Traits inheritance

_To be implemented:_
  - [Libraries](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#libraries)
  - [User defined facets](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#user-defined-facets)
  - Full implementation of [type expressions](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#type-expressions)
	- The shorthand array and the union type have been implemented
	- Bi-dimensional array and the array-union combination have **NOT** been implemented yet.
  - [Multiple inheritance](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#multiple-inheritance)
  - [Annotations](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#annotations)
  - [Overlays and Extensions](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#overlays-and-extensions)
  - [Improved Security Schemes](https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#security-schemes)

### Get started
Requires:

- composer (see [https://getcomposer.org](https://getcomposer.org))
 
```bash
composer require raml-org/raml-php-parser
```

```php
$parser = new \Raml\Parser();
$apiDef = $parser->parse($filename, true);

$title = $apiDef->getTitle();
```

### Parsing schemas
The library can convert schemas into an validation object. There is a default list, or they can be configured manually.
Each schema parser needs to conform to `Raml\Schema\SchemaParserInterface` and will return a instance of 
`Raml\Schema\SchemaDefinitionInterface`.

Additional parsers and schema definitions can be created and passed into the `Raml\Parser` constructor

### Exporting routes
It is also possible to export the entire RAML file to an array of the full endpoints. For example, considering
a [basic RAML](https://github.com/raml-org/raml-php-parser/blob/master/tests/fixture/simple.raml), this can be
returned using:


```php
$parser = new \Raml\Parser();
$api = $parser->parse('tests/fixture/simple.raml');

$routes = $api->getResourcesAsUri();
```

To return:
```php
[
    GET /songs => ...
    POST /songs => ...
    GET /songs/{songId} => ...
    DELETE /songs/{songId} => ...
]

$routes = $api->getResourcesAsUri(new \Raml\RouteFormatter\NoRouteFormatter());
```

#### Route Formatters
There are two Route Formatters included in the package:

- `NoRouteFormatter` which does nothing and simply echoes the result
- `SymfonyRouteFormatter` which adds the routes to a Symfony `RouteCollection`

### Contributing
```bash
composer validate-files
composer run-static-analysis
composer check-code-style
composer run-tests
```
