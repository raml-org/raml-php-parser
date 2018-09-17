<?php

namespace Raml\Tests;

use PHPUnit\Framework\TestCase;
use Raml\ApiDefinition;
use Raml\Body;
use Raml\Exception\BadParameter\FileNotFoundException;
use Raml\Exception\BadParameter\ResourceNotFoundException;
use Raml\Exception\InvalidJsonException;
use Raml\Exception\InvalidQueryParameterTypeException;
use Raml\Exception\RamlParserException;
use Raml\Method;
use Raml\NamedParameter;
use Raml\ParseConfiguration;
use Raml\Parser;
use Raml\Resource;
use Raml\Response;
use Raml\Schema\Definition\JsonSchemaDefinition;
use Raml\Schema\Parser\JsonSchemaParser;
use Raml\Schema\SchemaDefinitionInterface;
use Raml\Schema\SchemaParserInterface;
use Raml\SecurityScheme;

class ParseTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    /**
     * @test
     */
    public function shouldCorrectlyLoadASimpleRamlString()
    {
        $raml = <<<RAML
#%RAML 0.8
title: ZEncoder API
documentation:
 - title: Home
   content: |
     Welcome to the _Zencoder API_ Documentation. The _Zencoder API_
     allows you to connect your application to our encoding service
     and encode videos without going through the web  interface. You
     may also benefit from one of our
     [integration libraries](https://app.zencoder.com/docs/faq/basics/libraries)
     for different languages.
version: v2
baseUri: https://app.zencoder.com/api/{version}
RAML;

        $simpleRaml = $this->parser->parseFromString($raml, '');
        $docList = $simpleRaml->getDocumentationList();

        $this->assertEquals('ZEncoder API', $simpleRaml->getTitle());
        $this->assertEquals('Home', $docList[0]['title']);
        $this->assertEquals('v2', $simpleRaml->getVersion());
    }

    /**
     * @test
     */
    public function shouldCorrectlyLoadASimpleRamlStringWithInclude()
    {
        $raml = <<<RAML
#%RAML 0.8
title: ZEncoder API
documentation: !include child.raml
version: v2
baseUri: https://app.zencoder.com/api/{version}
RAML;

        $simpleRaml = $this->parser->parseFromString($raml, __DIR__.'/fixture');
        $docList = $simpleRaml->getDocumentationList();

        $this->assertEquals('ZEncoder API', $simpleRaml->getTitle());
        $this->assertEquals('Home', $docList['title']);
        $this->assertEquals('v2', $simpleRaml->getVersion());
        $this->assertEquals([__DIR__.'/fixture/child.raml'], $this->parser->getIncludedFiles());
    }

    /**
     * @test
     */
    public function shouldCorrectlyLoadASimpleRamlFile()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $this->assertEquals('World Music API', $simpleRaml->getTitle());
        $this->assertEquals('v1', $simpleRaml->getVersion());
        $this->assertEquals('http://example.api.com/v1', $simpleRaml->getBaseUri());
        $this->assertEquals(['application/json'], $simpleRaml->getDefaultMediaTypes());
    }

    /**
     * @test
     */
    public function shouldThrowCorrectExceptionOnBadJson()
    {
        $this->expectException(InvalidJsonException::class);
        $this->parser->parse(__DIR__.'/fixture/invalid/badJson.raml');
    }

    /**
     * @test
     */
    public function shouldThrowFileNotFoundExceptionOnBadRamlFileWithNotExistingFile()
    {
        $this->expectException(FileNotFoundException::class);
        $this->parser->parse(__DIR__.'/fixture/invalid/bad.raml');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnPathManipulationIfNotAllowed()
    {
        $config = new \Raml\ParseConfiguration();
        $config->disableDirectoryTraversal();
        $this->parser->setConfiguration($config);
        $this->expectException(FileNotFoundException::class);
        $this->parser->parse(__DIR__.'/fixture/treeTraversal/bad.raml');
    }

    /**
     * @test
     */
    public function shouldPreventDirectoryTraversalByDefault()
    {
        $this->expectException(FileNotFoundException::class);
        $this->parser->parse(__DIR__.'/fixture/treeTraversal/bad.raml');
    }

    /**
     * @test
     */
    public function shouldNotThrowExceptionOnPathManipulationIfAllowed()
    {
        $config = new \Raml\ParseConfiguration();
        $config->enableDirectoryTraversal();
        $this->parser->setConfiguration($config);

        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/treeTraversal/bad.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');

        $schema = $body->getSchema();
        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
    public function shouldCorrectlyReturnHttpProtocol()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $this->assertTrue($simpleRaml->supportsHttp());
        $this->assertFalse($simpleRaml->supportsHttps());
    }

    /**
     * @test
     */
    public function shouldReturnAResourceObjectForAResource()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $this->assertInstanceOf(Resource::class, $resource);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfUriNotFound()
    {
        $this->expectException(ResourceNotFoundException::class);
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');

        try {
            $simpleRaml->getResourceByUri('/invalid');
        } catch (\Raml\Exception\BadParameter\ResourceNotFoundException $e) {
            $this->assertEquals('/invalid', $e->getUri());

            throw $e;
        }
    }

    /**
     * @test
     */
    public function shouldNotMatchForwardSlashInURIParameter()
    {
        $this->expectException(ResourceNotFoundException::class);
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $simpleRaml->getResourceByUri('/songs/1/e');
    }

    /**
     * @test
     */
    public function shouldNotMatchForwardSlashAndDuplicationInURIParameter()
    {
        $this->expectException(ResourceNotFoundException::class);
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $simpleRaml->getResourceByUri('/songs/1/1');
    }

    /**
     * @test
     */
    public function shouldGiveTheResourceTheCorrectDisplayNameIfNotProvided()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $this->assertEquals('/songs', $resource->getDisplayName());
    }

    /**
     * @test
     */
    public function shouldExcludeQueryParametersWhenFindingAResource()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs?1');
        $this->assertEquals('/songs', $resource->getDisplayName());
    }

    /**
     * @test
     */
    public function shouldGiveTheResourceTheCorrectDisplayNameIfProvided()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/traitsAndTypes.raml');
        $resource = $simpleRaml->getResourceByUri('/dvds');
        $this->assertEquals('DVD', $resource->getDisplayName());
    }

    /**
     * @test
     */
    public function shouldParseMultiLevelUrisAndParameters()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');

        $resource = $simpleRaml->getResourceByUri('/songs/1');
        $this->assertEquals('/songs/{songId}', $resource->getDisplayName());
    }

    /**
     * @test
     */
    public function shouldReturnAMethodObjectForAMethod()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/1');
        $method = $resource->getMethod('post');
        /** @var Body $body */
        $body = $method->getBodyByType('application/json');
        $schema = $body->getSchema();

        $this->assertCount(3, $resource->getMethods());
        $this->assertInstanceOf(Method::class, $method);
        $this->assertEquals('POST', $method->getType());
        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
    public function shouldReturnAResponseForAResponse()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/1');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);

        $this->assertNotEmpty($method->getResponses());
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     */
    public function shouldReturnAnExampleForType()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/1');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);

        $this->assertEquals(['application/json', 'application/xml'], $response->getTypes());

        /** @var Body $body */
        $body = $response->getBodyByType('application/json');

        $schema = $body->getExample();

        $this->assertEquals(
            [
                'title' => 'Wish You Were Here',
                'artist' => 'Pink Floyd',
            ],
            json_decode($schema, true)
        );
    }

    /**
     * @test
     */
    public function shouldParseJson()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/1');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
    public function shouldParseJsonSchemaInRaml()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/schemaInRoot.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
    public function shouldIncludeChildJsonObjects()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/parentAndChildSchema.raml');

        $resource = $simpleRaml->getResourceByUri('/');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
    public function shouldNotParseJsonIfNotRequested()
    {
        $config = new \Raml\ParseConfiguration();
        $config->disableSchemaParsing();
        $this->parser->setConfiguration($config);

        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/1');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $this->assertInternalType('string', $schema);
    }

    /**
     * @test
     */
    public function shouldParseJsonRefs()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        /** @var JsonSchemaDefinition $schema */
        $schema = $body->getSchema();
        $schemaObject = $schema->getJsonObject();

        $this->assertEquals('A canonical song', $schemaObject->items->description);
    }

    /**
     * @test
     */
    public function shouldParseJsonIntoArray()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();
        /** @var JsonSchemaDefinition $schema */
        $schemaArray = $schema->getJsonArray();

        $this->assertEquals('A canonical song', $schemaArray['items']['description']);
    }

    /**
     * @test
     */
    public function shouldParseIncludedJson()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
    public function shouldNotParseIncludedJsonIfNotRequired()
    {
        $config = new ParseConfiguration();
        $config->disableSchemaParsing();
        $this->parser->setConfiguration($config);

        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        $schema = $body->getSchema();

        $this->assertInternalType('string', $schema);
    }

    /**
     * @test
     */
    public function shouldParseIncludedJsonRefs()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        /** @var JsonSchemaDefinition $schema */
        $schema = $body->getSchema();
        $schemaObject = $schema->getJsonObject();

        $this->assertEquals('A canonical song', $schemaObject->items->description);
    }

    /**
     * @test
     */
    public function shouldSetCorrectSourceUriOnSchemaParsers()
    {
        $schemaParser = $this->createMock(SchemaParserInterface::class);
        $schemaParser->method('createSchemaDefinition')->willReturn(
            $this->createMock(SchemaDefinitionInterface::class)
        );
        $schemaParser->method('getCompatibleContentTypes')->willReturn(['application/json']);
        $schemaParser->method('setSourceUri')->withConsecutive(
            ['file://'.__DIR__.'/fixture/songs.json']
        );

        $parser = new \Raml\Parser(
            [
                $schemaParser,
            ]
        );

        $parser->parse(__DIR__.'/fixture/includeSchema.raml');
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfEmpty()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('RAML file appears to be empty');
        $this->parser->parse(__DIR__.'/fixture/invalid/empty.raml');
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfNoTitle()
    {
        $this->expectException(RamlParserException::class);
        $this->parser->parse(__DIR__.'/fixture/invalid/noTitle.raml');
    }

    /**
     * @test
     */
    public function shouldBeAbleToAddAdditionalSchemaTypes()
    {
        $schemaParser = new JsonSchemaParser();
        $schemaParser->addCompatibleContentType('application/vnd.api-v1+json');
        $this->parser->addSchemaParser($schemaParser);
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/includeUnknownSchema.raml');

        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);

        /** @var Body $body */
        $body = $response->getBodyByType('application/vnd.api-v1+json');
        $schema = $body->getSchema();


        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
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

        $this->assertEquals(
            'Return books that have their title matching the given value for path /books',
            $queryParameters['title']->getDescription()
        );
        $this->assertEquals(
            'If no values match the value given for title, use digest_all_fields instead',
            $queryParameters['digest_all_fields']->getDescription()
        );
        $this->assertEquals('A valid access_token is required', $queryParameters['access_token']->getDescription());
        $this->assertEquals('The number of pages to return', $queryParameters['numPages']->getDescription());

        $resource = $traitsAndTypes->getResourceByUri('/dvds');
        $method = $resource->getMethod('get');
        $queryParameters = $method->getQueryParameters();

        $this->assertEquals(
            'Return DVD that have their title matching the given value for path /dvds',
            $queryParameters['title']->getDescription()
        );
    }

    /**
     * @test
     */
    public function shouldParseIncludedRaml()
    {
        $parent = $this->parser->parse(__DIR__.'/fixture/includeRaml.raml');

        $documentation = $parent->getDocumentationList();
        $this->assertEquals('Home', $documentation['title']);
        $this->assertEquals('Welcome to the _Zencoder API_ Documentation', $documentation['content']);
    }

    /**
     * @test
     */
    public function shouldParseIncludedYaml()
    {
        $parent = $this->parser->parse(__DIR__.'/fixture/includeYaml.raml');

        $documentation = $parent->getDocumentationList();
        $this->assertEquals('Home', $documentation['title']);
        $this->assertEquals('Welcome to the _Zencoder API_ Documentation', $documentation['content']);
    }

    /**
     * @test
     */
    public function shouldIncludeTraits()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $queryParameters = $method->getQueryParameters();
        $queryParameter = $queryParameters['pages'];

        $this->assertEquals('The number of pages to return', $queryParameter->getDescription());
        $this->assertEquals('number', $queryParameter->getType());
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfPassedFileDoesNotExist()
    {
        $fileName = __DIR__.'/fixture/gone.raml';

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage(sprintf('The file %s does not exist or is unreadable.', $fileName));

        try {
            $this->parser->parse($fileName);
        } catch (FileNotFoundException $e) {
            $this->assertEquals($fileName, $e->getFileName());

            throw $e;
        }
    }

    /**
     * @test
     */
    public function shouldParseHateoasExample()
    {
        $hateoasRaml = $this->parser->parse(__DIR__.'/fixture/hateoas/example.raml');
        $this->assertInstanceOf(ApiDefinition::class, $hateoasRaml);
    }

    /**
     * @test
     */
    public function shouldParseMethodDescription()
    {
        $methodDescriptionRaml = $this->parser->parse(__DIR__.'/fixture/methodDescription.raml');
        $this->assertEquals(
            'Get a list of available songs',
            $methodDescriptionRaml->getResourceByUri('/songs')->getMethod('get')->getDescription()
        );
    }

    /**
     * @test
     */
    public function shouldParseResourceDescription()
    {
        $resourceDescriptionRaml = $this->parser->parse(__DIR__.'/fixture/resourceDescription.raml');
        $this->assertEquals(
            'Collection of available songs resource',
            $resourceDescriptionRaml->getResourceByUri('/songs')->getDescription()
        );
    }

    /**
     * @test
     */
    public function shouldParseStatusCode()
    {
        $simpleRaml = $this->parser->parse(__DIR__.'/fixture/simple.raml');
        $resource = $simpleRaml->getResourceByUri('/songs/1');
        $response = $resource->getMethod('get')->getResponse(200);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldParseMethodHeaders()
    {
        $headersRaml = $this->parser->parse(__DIR__.'/fixture/headers.raml');
        $resource = $headersRaml->getResourceByUri('/jobs');

        $this->assertEquals(
            [
                'Zencoder-Api-Key' => NamedParameter::createFromArray(
                    'Zencoder-Api-Key',
                    ['displayName' => 'ZEncoder API Key']
                ),
            ],
            $resource->getMethod('post')->getHeaders()
        );
    }

    /**
     * @test
     */
    public function shouldParseResponseHeaders()
    {
        $headersRaml = $this->parser->parse(__DIR__.'/fixture/headers.raml');
        $resource = $headersRaml->getResourceByUri('/jobs');

        $this->assertEquals(
            [
                'X-waiting-period' => NamedParameter::createFromArray(
                    'X-waiting-period',
                    [
                        'description' => 'The number of seconds to wait before you can attempt to make a request again.'."\n",
                        'type' => 'integer',
                        'required' => 'yes',
                        'minimum' => 1,
                        'maximum' => 3600,
                        'example' => 34,
                    ]
                ),
            ],
            $resource->getMethod('post')->getResponse(503)->getHeaders()
        );
    }

    /**
     * @test
     */
    public function shouldReplaceReservedParameter()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/reservedParameter.raml');
        $this->assertEquals(
            'Get list of songs at /songs',
            $def->getResourceByUri('/songs')->getMethod('get')->getDescription()
        );
    }

    /**
     * @test
     */
    public function shouldParameterTransformerWorks()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/parameterTransformer.raml');
        $this->assertEquals(
            'songs /songs song /song',
            $def->getResourceByUri('/songs')->getMethod('post')->getDescription()
        );
        $this->assertEquals(
            'song /song songs /songs',
            $def->getResourceByUri('/song')->getMethod('get')->getDescription()
        );
    }

    /**
     * @test
     */
    public function shouldParseSchemasDefinedInTheRoot()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/rootSchemas.raml');

        $this->assertCount(2, $def->getSchemaCollections());
    }

    /**
     * @test
     */
    public function shouldCorrectlyHandleQueryParameters()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/queryParameters.raml');

        $resource = $def->getResourceByUri('/books/1');
        $method = $resource->getMethod('get');
        $queryParameters = $method->getQueryParameters();

        $this->assertEquals(3, count($queryParameters));

        $this->assertEquals('integer', $queryParameters['page']->getType());
        $this->assertEquals('Current Page', $queryParameters['page']->getDisplayName());
        $this->assertNull($queryParameters['page']->getDescription());
        $this->assertNull($queryParameters['page']->getExample());
        $this->assertFalse($queryParameters['page']->isRequired());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnBadQueryParameter()
    {
        $this->expectException(InvalidQueryParameterTypeException::class);

        try {
            $this->parser->parse(__DIR__.'/fixture/invalid/queryParameters.raml');
        } catch (InvalidQueryParameterTypeException $e) {
            $this->assertEquals('invalid', $e->getType());
            $this->assertEquals(
                [
                    'string',
                    'number',
                    'integer',
                    'date',
                    'boolean',
                    'file',
                    'datetime-only',
                    'date-only',
                    'time-only',
                    'datetime',
                    'array',
                ],
                $e->getValidTypes()
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function shouldReplaceParameterByJsonString()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/jsonStringExample.raml');
        /** @var Body $body */
        $body = $def->getResourceByUri('/songs')->getMethod('get')->getResponse(200)->getBodyByType('application/json');
        $example = $body->getExample();

        $this->assertEquals(
            [
                'items' => [
                    [
                        'id' => 2,
                        'title' => 'test',
                    ],
                ],
            ],
            json_decode($example, true)
        );
    }

    // ---

    /**
     * @test
     */
    public function shouldParseSecuritySchemes()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/securitySchemes.raml');

        $resource = $def->getResourceByUri('/users');
        $method = $resource->getMethod('get');

        $securitySchemes = $method->getSecuritySchemes();

        $this->assertEquals(2, count($securitySchemes));
        $this->assertInstanceOf(SecurityScheme::class, $securitySchemes['oauth_1_0']);
        $this->assertInstanceOf(SecurityScheme::class, $securitySchemes['oauth_2_0']);

        $this->assertEquals(
            'OAuth 1.0 continues to be supported for all API requests, but OAuth 2.0 is now preferred.',
            trim($securitySchemes['oauth_1_0']->getDescription())
        );

        $this->assertEquals(
            'OAuth 1.0',
            $securitySchemes['oauth_1_0']->getType()
        );

        $settings = new \Raml\SecurityScheme\SecuritySettings\OAuth1SecuritySettings();
        $settings->setRequestTokenUri('https://api.dropbox.com/1/oauth/request_token');
        $settings->setAuthorizationUri('https://www.dropbox.com/1/oauth/authorize');
        $settings->setTokenCredentialsUri('https://api.dropbox.com/1/oauth/access_token');

        $this->assertEquals(
            $settings,
            $securitySchemes['oauth_1_0']->getSettings()
        );
    }

    /**
     * @test
     */
    public function shouldAddHeadersOfSecuritySchemes()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/securitySchemes.raml');

        $resource = $def->getResourceByUri('/users');
        $method = $resource->getMethod('get');
        $headers = $method->getHeaders();

        $this->assertEquals(1, count($headers));
        $this->assertInstanceOf(NamedParameter::class, $headers['Authorization']);
    }

    /**
     * @test
     */
    public function shouldReplaceSchemaByRootSchema()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/replaceSchemaByRootSchema.raml');
        $response = $def->getResourceByUri('/songs/{id}')->getMethod('get')->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('application/json');
        /** @var JsonSchemaDefinition $schema */
        $schema = $body->getSchema();

        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);

        $schema = $schema->getJsonArray();

        $this->assertCount(2, $schema['properties']);
    }

    /**
     * @test
     */
    public function shouldParseAndReplaceSchemaOnlyInResources()
    {
        $def = $this->parser->parse(__DIR__.'/fixture/schemaInTypes.raml');
        /** @var Body $body */
        $body = $def->getResourceByUri('/projects')->getMethod('post')->getBodyByType('application/json');
        $schema = $body->getSchema();
        $this->assertInstanceOf(JsonSchemaDefinition::class, $schema);
    }

    /**
     * @test
     */
    public function shouldParseInfoQExample()
    {
        $infoQ = $this->parser->parse(__DIR__.'/fixture/infoq/eventlog.raml');
        $this->assertEquals('Eventlog API', $infoQ->getTitle());
    }

    /**
     * @test
     */
    public function shouldLoadATree()
    {
        $tree = $this->parser->parse(__DIR__.'/fixture/includeTreeRaml.raml');
        $this->assertEquals('Test', $tree->getTitle());

        $resource = $tree->getResourceByUri('/songs');
        $methods = $resource->getMethods();

        $this->assertCount(1, $methods);
        $this->assertArrayHasKey('GET', $methods);

        $responses = $methods['GET']->getResponses();

        $this->assertCount(1, $responses);
        $this->assertArrayHasKey(200, $responses);

        $types = $responses[200]->getTypes();

        $this->assertCount(1, $types);
        $this->assertContains('application/json', $types);

        $jsonBody = $responses[200]->getBodyByType('application/json');

        $this->assertEquals('application/json', $jsonBody->getMediaType());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnInvalidBodyType()
    {
        $raml = <<<'RAML'
#%RAML 0.8
title: Test body
/:
  get:
    description: A post to do something
    responses:
      200:
        body:
          application/json:
            schema: |
              {
                "$schema": "http://json-schema.org/schema",
                "type": "array"
              }
RAML;


        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);

        $response->getBodyByType('application/json');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No body found for type "text/xml"');

        $response->getBodyByType('text/xml');
    }

    /**
     * @test
     */
    public function shouldSupportGenericResponseType()
    {
        $raml = <<<'RAML'
#%RAML 0.8
title: Test body
/:
  get:
    description: A post to do something
    responses:
      200:
        body:
          "*/*":
            description: A generic description
RAML;


        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        /** @var Body $body */
        $body = $response->getBodyByType('text/xml');
        $this->assertEquals('A generic description', $body->getDescription());
    }

    /**
     * @test
     */
    public function shouldMergeMethodSecurityScheme()
    {
        $apiDefinition = $this->parser->parse(__DIR__.'/fixture/securitySchemes.raml');
        $resource = $apiDefinition->getResourceByUri('/users');
        $method = $resource->getMethod('get');
        $headers = $method->getHeaders();
        $this->assertFalse(empty($headers['Authorization']));
    }

    /**
     * @test
     */
    public function shouldAddSecuritySchemeToResource()
    {
        $apiDefinition = $this->parser->parse(__DIR__.'/fixture/resourceSecuritySchemes.raml');
        $resource = $apiDefinition->getResourceByUri('/users');
        $method = $resource->getMethod('get');
        $schemes = $method->getSecuritySchemes();
        $this->assertArrayHasKey('oauth_1_0', $schemes);
        $this->assertArrayHasKey('oauth_2_0', $schemes);
        $this->assertArrayHasKey('', $schemes);
    }

    /**
     * @test
     */
    public function shouldParseCustomSettingsOnResource()
    {
        $apiDefinition = $this->parser->parse(__DIR__.'/fixture/securedByCustomProps.raml');
        $resource = $apiDefinition->getResourceByUri('/test');
        $method = $resource->getMethod('get');
        $schemes = $method->getSecuritySchemes();
        $this->assertArrayHasKey('custom', $schemes);
        $this->assertArrayHasKey('oauth_2_0', $schemes);

        $this->assertEquals($schemes['custom']->getSettings()['myKey'], 'heLikesItNotSoMuch');
    }

    /**
     * @test
     */
    public function shouldParseCustomSettingsOnMethodWithOAuthParser()
    {
        $apiDefinition = $this->parser->parse(__DIR__.'/fixture/securedByCustomProps.raml');
        $resource = $apiDefinition->getResourceByUri('/test');
        $method = $resource->getMethod('get');
        $schemes = $method->getSecuritySchemes();
        $settingsObject = $schemes['oauth_2_0']->getSettings();
        $this->assertSame($settingsObject->getScopes(), ['ADMINISTRATOR', 'USER']);
        $this->assertSame($settingsObject->getAuthorizationUri(), 'https://www.dropbox.com/1/oauth2/authorize');
    }

    /**
     * @test
     */
    public function shouldParseIncludedTraits()
    {
        $apiDefinition = $this->parser->parse(__DIR__.'/fixture/includedTraits.raml');
        $resource = $apiDefinition->getResourceByUri('/category');
        $method = $resource->getMethod('get');
        $queryParams = $method->getQueryParameters();

        $this->assertCount(3, $queryParams);
        $this->assertSame(['id', 'parent_id', 'title'], array_keys($queryParams));
    }

    /**
     * @test
     */
    public function shouldParseResourcePathNameCorrectly()
    {
        $apiDefinition = $this->parser->parse(__DIR__.'/fixture/resourcePathName.raml');

        $foo = $apiDefinition->getResources()['/foo'];
        /** @var Resource $fooId */
        $fooId = $foo->getResources()['/foo/{fooId}'];
        /** @var Resource $bar */
        $bar = $fooId->getResources()['/foo/{fooId}/bar'];

        $this->assertEquals('Get a list of foo', $foo->getDescription());
        $this->assertEquals('Get a single foo', $fooId->getDescription());
        $this->assertEquals('Get a list of bar', $bar->getDescription());

        $baz = $apiDefinition->getResources()['/baz'];
        /** @var Resource $bazId */
        $bazId = $baz->getResources()['/baz/{bazId}'];
        /** @var Response $qux */
        $qux = $bazId->getResources()['/baz/{bazId}/qux'];

        $this->assertEquals('Get a list of bazDisplayname', $baz->getDescription());
        $this->assertEquals('Get a single bazDisplayname', $bazId->getDescription());
        $this->assertEquals('Get a list of quxDisplayname', $qux->getDescription());
    }

    /**
     * @test
     */
    public function shouldNestedResourcesHaveParentResourceDefined()
    {
        $apiDefinition = $this->parser->parse(__DIR__.'/fixture/resourcePathName.raml');

        $foo = $apiDefinition->getResources()['/foo'];
        /** @var Resource $fooId */
        $fooId = $foo->getResources()['/foo/{fooId}'];
        /** @var Resource $bar */
        $bar = $fooId->getResources()['/foo/{fooId}/bar'];

        $this->assertEquals($fooId, $bar->getParentResource());
        $this->assertEquals($foo, $fooId->getParentResource());
        $this->assertEquals(null, $foo->getParentResource());
    }

    /**
     * @test
     */
    public function shouldParseTraits()
    {
        $raml = <<<RAML
#%RAML 1.0
title: ZEncoder API
version: v2
baseUri: https://app.zencoder.com/api/{version}

traits:
  secured:
    usage: Apply this to any method that needs to be secured
    description: Some requests require authentication.
    headers:
      access_token:
        description: Access Token
        example: 5757gh76
        required: true
    queryParameters:
      <<tokenName>>:
        description: A valid <<tokenName>> is required
  paged:
    queryParameters:
      page:
        type: string
        required: true
  filtered:
    is: [paged]
    queryParameters:
      limit:
        type: integer
        required: false
      offset:
        type: integer
        required: false
/users:
  is: [secured: { tokenName: access_token }]
  get:
    is: [filtered]
RAML;

        $simpleRaml = $this->parser->parseFromString($raml, '');

        $this->assertCount(3, $simpleRaml->getTraits()->toArray());
        $this->assertCount(1, $simpleRaml->getTraits()->getTraitByName('filtered')->getTraits());
        $this->assertCount(1, $simpleRaml->getResourceByPath('/users')->getTraits());
        $this->assertCount(2, $simpleRaml->getResourceByPath('/users')->getMethod('get')->getTraits());
    }
}
