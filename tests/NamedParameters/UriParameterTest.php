<?php

namespace Raml\Tests\NamedParameters;

use PHPUnit\Framework\TestCase;
use Raml\Parser;
use Raml\Resource;

class UriParameterTest extends TestCase
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
        $this->assertInstanceOf(Resource::class, $resource);
    }

    /**
     * @test
     */
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
        $this->assertInstanceOf(Resource::class, $resource);
    }

    /**
     * @test
     */
    public function shouldCorrectlyParseEnumUriParameters()
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
        enum: [one, two]        
    get:
      displayName: retrieve a user's picture by user name
RAML;

        $apiDef = $this->parser->parseFromString($raml, '');
        $resource = $apiDef->getResourceByUri('/user/one');
        $this->assertInstanceOf(Resource::class, $resource);
    }

    /**
     * @test
     */
    public function shouldPassUriParametersFromParentToSub()
    {
        $raml = <<<RAML
#%RAML 0.8
title: User API
version: 1.0

/base/{param1}:
  uriParameters:
    param1:
      type: string

  /sub/{param2}:
    uriParameters:
      param2:
        type: string        
RAML;

        $apiDef = $this->parser->parseFromString($raml, '');

        $resource = $apiDef->getResourceByPath('/base/{param1}/sub/{param2}');
        $this->assertInstanceOf(Resource::class, $resource);

        $uriParameters = $resource->getUriParameters();

        $this->assertCount(2, $uriParameters, 'should contain 2 uri parameters');

        $this->assertArrayHasKey('param1', $uriParameters, 'should contain uri parameter from parent');
        $this->assertArrayHasKey('param2', $uriParameters, 'should contain uri parameter from sub');
    }
}
