Experimental branch! Introduces major backwards incompatibilities!

[![Build Status](https://travis-ci.org/alecsammon/php-raml-parser.svg?branch=master)](https://travis-ci.org/alecsammon/php-raml-parser)
[![Coverage Status](https://img.shields.io/coveralls/alecsammon/php-raml-parser.svg)](https://coveralls.io/r/alecsammon/php-raml-parser?branch=master)
[![HHVM Status](http://hhvm.h4cc.de/badge/alecsammon/php-raml-parser.png)](http://hhvm.h4cc.de/package/alecsammon/php-raml-parser)

Parses a RAML file into a PHP object.
Converts any JSON schemas into a PHP object - see https://github.com/justinrainbow/json-schema

Get started:
```php
./composer.phar install --dev
./vendor/bin/phpunit test
```

```php
$parser = new \Raml\Parser();
$apiDef = $parser->parse($filename);

$title = $apiDef->getTitle();
```

Contributing:
```php
./vendor/bin/phpunit test
./vendor/bin/phpcs --standard=PSR1,PSR2 src
```

@todo
- Make the code prettier
- Add support for the complete raml spec
    - SecuredBy
    - Markdown?
    - Date Representations?
- Validation?

Supported (I Believe)
- Includes
    - .yml/.yaml
    - .raml/.rml
    - .json (parsed using json-schema)
- Display Name
- Traits
- Resource Types
