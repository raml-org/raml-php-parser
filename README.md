# PHP RAML Parser

Parses a RAML file into a PHP object.

[![Build Status](https://travis-ci.org/alecsammon/php-raml-parser.svg?branch=master)](https://travis-ci.org/alecsammon/php-raml-parser)
[![Coverage Status](https://img.shields.io/coveralls/alecsammon/php-raml-parser.svg)](https://coveralls.io/r/alecsammon/php-raml-parser?branch=master)
[![HHVM Status](http://hhvm.h4cc.de/badge/alecsammon/php-raml-parser.png)](http://hhvm.h4cc.de/package/alecsammon/php-raml-parser)

See the RAML spec here: https://github.com/raml-org/raml-spec

### Get started
Requires:

- composer (see [https://getcomposer.org](https://getcomposer.org))
 
```bash
composer install --dev
./vendor/bin/phpunit test
```

```php
$parser = new \Raml\Parser();
$apiDef = $parser->parse($filename, true);

$title = $apiDef->getTitle();
```

### Parsing schemas
The library can convert schemas into an validation object. There is a default list, or they can be configured manually.
Each schema parser needs to conform to `\Raml\Schema\SchemaParserInterface` and will return a instance of 
`\Raml\Schema\SchemaDefinitionInterface`.

Additional parsers and schema definitions can be created and passed into the `\Raml\Parser` constructor

### Exporting routes
It is also possible to export the entire RAML file to an array of the full endpoints. For example, considering
a [basic RAML](https://github.com/alecsammon/php-raml-parser/blob/master/test/fixture/simple.raml), this can be
returned using:


```php
$parser = new Raml\Parser();
$api = $parser->parse('test/fixture/simple.raml');

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

$routes = $api->getResourcesAsUri(new Raml\RouteFormatter\NoRouteFormatter());
```

#### Route Formatters
There are two Route Formatters included in this package:

- `NoRouteFormatter` which does nothing and simply echoes the result
- `SymfonyRouteFormatter` which adds the routes to a Symfony `RouteCollection`

### Contributing
```bash
./vendor/bin/phpunit
./vendor/bin/phpunit --coverage-text
./vendor/bin/phpcs --standard=PSR1,PSR2 src
```

### TODO
- Documentation/Markdown parser
- Date Representations?
- Parse RAML at provided URL

### Supported (I Believe)
- Includes
    - .yml/.yaml
    - .raml/.rml
    - .json (parsed using json-schema)
- Display Name
- Traits
- Resource Types

