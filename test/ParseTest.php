<?php

class ParseTest extends PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new \Raml\Parser();
    }

    /** @test */
    public function shouldCorrectlyLoadASimpleRamlFile()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $this->assertEquals('World Music API', $simpleRaml['title']);
    }

    /** @test */
    public function shouldIncludeTraits()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $this->assertArrayHasKey('queryParameters', $simpleRaml['/songs']);
        $this->assertEquals([
            "description" => "The number of pages to return",
            "type" => "number"
        ], $simpleRaml['/songs']['queryParameters']['pages']);
    }

    /** @test */
    public function shouldApplyTraitVariables()
    {
        $traitsAndTypes = $this->parser->parse(__DIR__.'/fixture/traitsAndTypes.raml');

        $this->assertArrayHasKey('queryParameters', $traitsAndTypes['/books']['get']);

        $this->assertArrayHasKey('title', $traitsAndTypes['/books']['get']['queryParameters']);
        $this->assertArrayHasKey('digest_all_fields', $traitsAndTypes['/books']['get']['queryParameters']);
        $this->assertArrayHasKey('access_token', $traitsAndTypes['/books']['get']['queryParameters']);
        $this->assertArrayHasKey('numPages', $traitsAndTypes['/books']['get']['queryParameters']);

        $this->assertEquals('Return books that have their title matching the given value for path /books', $traitsAndTypes['/books']['get']['queryParameters']['title']['description']);
        $this->assertEquals('If no values match the value given for title, use digest_all_fields instead', $traitsAndTypes['/books']['get']['queryParameters']['digest_all_fields']['description']);
        $this->assertEquals('A valid access_token is required', $traitsAndTypes['/books']['get']['queryParameters']['access_token']['description']);
        $this->assertEquals('The number of pages to return, not to exceed 10', $traitsAndTypes['/books']['get']['queryParameters']['numPages']['description']);

        $this->assertEquals('Return DVD that have their title matching the given value for path /dvds', $traitsAndTypes['/dvds']['get']['queryParameters']['title']['description']);
    }
}