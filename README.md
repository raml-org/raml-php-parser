[![Build Status](https://travis-ci.org/alecsammon/php-raml-parser.svg?branch=master)](https://travis-ci.org/alecsammon/php-raml-parser)

[![Coverage Status](https://img.shields.io/coveralls/alecsammon/php-raml-parser.svg)](https://coveralls.io/r/alecsammon/php-raml-parser?branch=master)

Parses a RAML file into a PHP array.
Converts any JSON schemas into a PHP object - see https://github.com/justinrainbow/json-schema

```php
$parser = new \Raml\Parser();
$raml = $parser->parse($filename);
```

Run the tests
```php
./vendor/bin/phpunit test
```
