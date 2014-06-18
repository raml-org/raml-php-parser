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
