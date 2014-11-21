# PHP RAML Parser

Parses a RAML file into a PHP object.

[![Build Status](https://travis-ci.org/alecsammon/php-raml-parser.svg?branch=master)](https://travis-ci.org/alecsammon/php-raml-parser)
[![Coverage Status](https://img.shields.io/coveralls/alecsammon/php-raml-parser.svg)](https://coveralls.io/r/alecsammon/php-raml-parser?branch=master)
[![HHVM Status](http://hhvm.h4cc.de/badge/alecsammon/php-raml-parser.png)](http://hhvm.h4cc.de/package/alecsammon/php-raml-parser)

See the RAML spec here: https://github.com/raml-org/raml-spec

Converts JSON schemas into a PHP objects using https://github.com/justinrainbow/json-schema

### Get started
```
./composer.phar install --dev
./vendor/bin/phpunit test
```

```php
$parser = new \Raml\Parser();
$apiDef = $parser->parse($filename, true);

$title = $apiDef->getTitle();
```

### Exporting routes
It is possible to export the entire RAML file to an array of the full endpoints. For example, considering
a [basic RAML](https://github.com/alecsammon/php-raml-parser/blob/master/test/fixture/simple.raml), this can be
returned using:


```php
$parser = new Raml\Parser();
$api = $parser->parse('test/fixture/simple.raml');

$routes = $api->getResourcesAsUri();

[
	GET /songs => ...
	POST /songs => ...
	GET /songs/{songId} => ...
	DELETE /songs/{songId} => ...
]

$routes = $api->getResourcesAsUri(new Raml\Formatters\NoRouteFormatter());
```

#### Route Formatters
There are two Route Formatters included in this package:

- `NoRouteFormatter` which does nothing and simply echoes the result
- `SymfonyRouteFormatter` which adds the routes to a Symfony `RouteCollection`

### Contributing
```
./vendor/bin/phpunit
./vendor/bin/phpunit --coverage-text
./vendor/bin/phpcs --standard=PSR1,PSR2 src
```

### TODO
- Make the code prettier
- Add support for the complete raml spec
    - SecuredBy
    - Markdown?
    - Date Representations?
- Validation?
- Parse RAML at URL instead of file?

### Supported (I Believe)
- Includes
    - .yml/.yaml
    - .raml/.rml
    - .json (parsed using json-schema)
- Display Name
- Traits
- Resource Types

