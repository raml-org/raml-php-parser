<?php

class ParseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Raml\Parser
     */
    private $parser;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new \Raml\Parser();
    }

    // ---

    /** @test */
    public function shouldCorrectlyLoadASimpleRamlFile()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $this->assertEquals('World Music API', $simpleRaml->getTitle());
        $this->assertEquals('v1', $simpleRaml->getVersion());
        $this->assertEquals('http://example.api.com/{version}', $simpleRaml->getBaseUri());
        $this->assertNull($simpleRaml->getDefaultMediaType());
    }

    /** @test */
    public function shouldThrowCorrectExceptionOnBadJson()
    {
        $this->setExpectedException('Exception', 'Invalid JSON in '.__DIR__.'/fixture/invalid/bad.json');
        $this->parser->parse(__DIR__.'/fixture/invalid/badJson.raml');
    }

    /** @test */
    public function shouldIgnoreBadIncludeForRamlFile()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/invalid/bad.raml');
        $this->assertEquals('World Music API', $simpleRaml->getTitle());
    }

    /** @test */
    public function shouldCorrectlyReturnHttpProtocol()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $this->assertTrue($simpleRaml->supportsHttp());
        $this->assertFalse($simpleRaml->supportsHttps());
    }

    /** @test */
    public function shouldReturnAResourceObjectForAResource()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $this->assertInstanceOf('\Raml\Resource', $resource);
    }

    /** @test */
    public function shouldThrowExceptionIfUriNotFound()
    {
        $this->setExpectedException('Exception', 'Resource not found for uri "/invalid"');
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $simpleRaml->getResourceByUri('/invalid');
    }

    /** @test */
    public function shouldGiveTheResourceTheCorrectDisplayNameIfNotProvided()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $this->assertEquals('Songs', $resource->getDisplayName());
    }

    /** @test */
    public function shouldExcludeQueryParametersWhenFindingAResource()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs?1');
        $this->assertEquals('Songs', $resource->getDisplayName());
    }

    /** @test */
    public function shouldGiveTheResourceTheCorrectDisplayNameIfProvided()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/traitsAndTypes.raml');
        $resource = $simpleRaml->getResourceByUri('/dvds');
        $this->assertEquals('DVD', $resource->getDisplayName());
    }

    /** @test */
    public function shouldParseMultiLevelUrisAndParameters()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');

        $resource = $simpleRaml->getResourceByUri('/songs/{songId}');
        $this->assertEquals('{songId}', $resource->getDisplayName());
    }

    /** @test */
    public function shouldReturnAMethodObjectForAMethod()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/{songId]');
        $method = $resource->getMethod('post');
        $schema = $method->getSchemaByType('application/json');

        $this->assertCount(3, $resource->getMethods());
        $this->assertInstanceOf('\Raml\Method', $method);
        $this->assertEquals('POST', $method->getType());
        $this->assertInstanceOf('stdClass', $schema);
    }

    /** @test */
    public function shouldReturnAResponseForAResponse()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/{songId]');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);

        $this->assertNotEmpty($method->getResponses());
        $this->assertInstanceOf('\Raml\Response', $response);
    }

    /** @test **/
    public function shouldReturnAnExampleForType()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/{songId]');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);

        $schema = $response->getExampleByType('application/json');

        $this->assertInstanceOf('stdClass', $schema);
    }

    /** @test */
    public function shouldParseJson()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/{songId]');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $this->assertInstanceOf('stdClass', $schema);
    }

    /** @test */
    public function shouldNotParseJsonIfNotRequested()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml', false);
        $resource = $simpleRaml->getResourceByUri('/songs/{songId]');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $this->assertInternalType('string', $schema);
    }

    /** @test */
    public function shouldParseJsonRefs()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $this->assertEquals('A canonical song', $schema->items->description);
    }

    /** @test */
    public function shouldParseIncludedJson()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $this->assertInstanceOf('stdClass', $schema);
    }

    /** @test */
    public function shouldNotParseIncludedJsonIfNotRequired()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml', false);

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $this->assertInternalType('string', $schema);
    }

    /** @test */
    public function shouldParseIncludedJsonRefs()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $schema = $response->getSchemaByType('application/json');

        $this->assertEquals('A canonical song', $schema->items->description);
    }

    /** @test */
    public function shouldThrowErrorIfEmpty()
    {
        $this->setExpectedException('Exception', 'RAML file appears to be empty');
        $this->parser->parse(__DIR__.'/fixture/invalid/empty.raml');
    }

    /** @test */
    public function shouldThrowErrorIfNoTitle()
    {
        $this->setExpectedException('Exception', 'Key "title" not found in RAML');
        $this->parser->parse(__DIR__.'/fixture/invalid/noTitle.raml');
    }

    /** @test */
    public function shouldThrowErrorIfUnknownIncluded()
    {
        $this->setExpectedException('Exception', 'Extension "ini" not supported (yet)');
        $this->parser->parse(__DIR__.'/fixture/includeIni.raml');
    }

    /** @test */
    public function shouldApplyTraitVariables()
    {
        $traitsAndTypes = $this->parser->parse(__DIR__.'/fixture/traitsAndTypes.raml');

        $resource = $traitsAndTypes->getResourceByUri('/books');
        $method = $resource->getMethod('get');
        $queryParameters = $method->getQueryParameters();

        $this->assertArrayHasKey('title', $queryParameters);
        $this->assertArrayHasKey('digest_all_fields', $queryParameters);
        $this->assertArrayHasKey('access_token', $queryParameters);
        $this->assertArrayHasKey('numPages', $queryParameters);

        $this->assertEquals('Return books that have their title matching the given value for path /books', $queryParameters['title']->getDescription());
        $this->assertEquals('If no values match the value given for title, use digest_all_fields instead', $queryParameters['digest_all_fields']->getDescription());
        $this->assertEquals('A valid access_token is required', $queryParameters['access_token']->getDescription());
        $this->assertEquals('The number of pages to return', $queryParameters['numPages']->getDescription());

        $resource = $traitsAndTypes->getResourceByUri('/dvds');
        $method = $resource->getMethod('get');
        $queryParameters = $method->getQueryParameters();

        $this->assertEquals('Return DVD that have their title matching the given value for path /dvds', $queryParameters['title']->getDescription());
    }

    /** @test */
    public function shouldParseIncludedRaml()
    {
        $parent = $this->parser->parse(__DIR__.'/fixture/includeRaml.raml');

        $documentation = $parent->getDocumentation();
        $this->assertEquals('Home', $documentation['title']);
        $this->assertEquals('Welcome to the _Zencoder API_ Documentation', $documentation['content']);
    }

    /** @test */
    public function shouldParseIncludedYaml()
    {
        $parent = $this->parser->parse(__DIR__.'/fixture/includeYaml.raml');

        $documentation = $parent->getDocumentation();
        $this->assertEquals('Home', $documentation['title']);
        $this->assertEquals('Welcome to the _Zencoder API_ Documentation', $documentation['content']);
    }

    /** @test */
    public function shouldIncludeTraits()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('songs');
        $method = $resource->getMethod('get');
        $queryParameters = $method->getQueryParameters();
        $queryParameter = $queryParameters['pages'];

        $this->assertEquals('The number of pages to return', $queryParameter->getDescription());
        $this->assertEquals('number', $queryParameter->getType());
    }

    /** @test */
    public function shouldThrowErrorIfPassedFileDoesNotExist()
    {
        $this->setExpectedException('Exception', 'File does not exist');
        $this->parser->parse(__DIR__.'/fixture/gone.raml');
    }

    /** @test */
    public function shouldParseHateoasExample()
    {
        $hateoasRaml = $this->parser->parse(__DIR__.'/fixture/hateoas/example.raml');
        $this->assertInstanceOf('\Raml\ApiDefinition', $hateoasRaml);
    }

    /** @test */
    public function shouldParseMethodDescription()
    {
        $methodDescriptionRaml = $this->parser->parse(__DIR__.'/fixture/methodDescription.raml');
        $this->assertEquals('Get a list of available songs', $methodDescriptionRaml->getResourceByUri('songs')->getMethod('get')->getDescription());
    }

    /** @test */
    public function shouldParseResourceDescription()
    {
        $resourceDescriptionRaml = $this->parser->parse(__DIR__.'/fixture/resourceDescription.raml');
        $this->assertEquals('Collection of available songs resource', $resourceDescriptionRaml->getResourceByUri('songs')->getDescription());
    }

    /** @test */
    public function shouldParseStatusCode()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/{songId]');
        $response = $resource->getMethod('get')->getResponse(200);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function shouldParseMethodHeaders()
    {
        $headersRaml = $this->parser->parse(__DIR__.'/fixture/headers.raml');
        $resource = $headersRaml->getResourceByUri('/jobs');

        $this->assertEquals(['Zencoder-Api-Key' => [
            'displayName' => 'ZEncoder API Key'
        ]], $resource->getMethod('post')->getHeaders());
    }

    /** @test */
    public function shouldParseResponseHeaders()
    {
        $headersRaml = $this->parser->parse(__DIR__.'/fixture/headers.raml');
        $resource = $headersRaml->getResourceByUri('/jobs');

        $this->assertEquals(['X-waiting-period' => [
            'description' => 'The number of seconds to wait before you can attempt to make a request again.'."\n",
            'type' => 'integer',
            'required' => 'yes',
            'minimum' => 1,
            'maximum' => 3600,
            'example' => 34
        ]], $resource->getMethod('post')->getResponse(503)->getHeaders());
    }

    /** @test */
    public function shouldReplaceReservedParameter()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/reservedParameter.raml');
        $this->assertEquals('Get list of songs at /songs', $def->getResourceByUri('/songs')->getMethod('get')->getDescription());
    }

    /** @test */
    public function shouldParameterTransformerWorks()
    {
        $def = $this->parser->parse(__DIR__ . '/fixture/parameterTransformer.raml');
        $this->assertEquals('songs /songs song /song', $def->getResourceByUri('/songs')->getMethod('post')->getDescription());
        $this->assertEquals('song /song songs /songs', $def->getResourceByUri('/song')->getMethod('get')->getDescription());
    }

    /** @test */
    public function shouldParseSchemasDefinedInTheRoot()
    {
        $def = $this->parser->parse(__DIR__ . '/fixture/rootSchemas.raml');

        $this->assertCount(2, $def->getSchemas());
    }

    /** @test */
    public function shouldCorrectlyHandleQueryParameters()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/queryParameters.raml');

        $resource = $def->getResourceByUri('/books/{bookId}');
        $method = $resource->getMethod('get');
        $queryParameters = $method->getQueryParameters();

        $this->assertEquals(3, count($queryParameters));

        $this->assertEquals('integer', $queryParameters['page']->getType());
        $this->assertEquals('Current Page', $queryParameters['page']->getDisplayName());
        $this->assertNull($queryParameters['page']->getDescription());
        $this->assertNull($queryParameters['page']->getExample());
        $this->assertFalse($queryParameters['page']->isRequired());
    }

    /** @test */
    public function shouldThrowExceptionOnBadQueryParameter()
    {
        $this->setExpectedException('Exception', '"invalid" is not a valid type');
        $this->parser->parse(__DIR__.'/fixture/invalid/queryParameters.raml');
    }

    /** @test */
    public function shouldReplaceParameterByJsonString()
    {
        $def = $this->parser->parse(__DIR__ . '/fixture/jsonStringExample.raml');
        $example = $def->getResourceByUri('/songs')->getMethod('get')->getResponse(200)->getExampleByType('application/json');
        $this->assertTrue(is_object($example));
        $this->assertInstanceOf('stdClass', $example);
        $this->assertTrue(is_array($example->items));
        $this->assertCount(1, $example->items);
        $this->assertInstanceOf('stdClass', $example->items[0]);
    }
}
