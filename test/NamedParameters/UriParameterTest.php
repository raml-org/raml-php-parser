<?php
namespace Raml\Test\NamedParameters;

class UriParameterTest extends \PHPUnit_Framework_TestCase
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
    public function shouldCorrectlyParseBaseUriParameters()
    {
        $raml = <<<RAML
#%RAML 0.8
title: User API
version: 1.2
/user:
  /{userId}:
    displayName: Get a user
    uriParameters:
      userId:
        type: integer
    get:
      displayName: retrieve a user's picture
RAML;

        $apiDef = $this->parser->parseFromString($raml, '');
        $resource = $apiDef->getResourceByUri('/user/1');
        $this->assertInstanceOf('\Raml\Resource', $resource);
    }

    /** @test */
    public function shouldCorrectlyParseRegexUriParameters()
    {
        $raml = <<<RAML
#%RAML 0.8
title: User API
version: 1.2
/user:
  /{userName}:
    displayName: Get a user by name
    uriParameters:
      userName:
        type: string
        pattern: ^[a-z0-9]+$
    get:
      displayName: retrieve a user's picture by user name
RAML;

        $apiDef = $this->parser->parseFromString($raml, '');
        $resource = $apiDef->getResourceByUri('/user/alec');
        $this->assertInstanceOf('\Raml\Resource', $resource);
    }
}
