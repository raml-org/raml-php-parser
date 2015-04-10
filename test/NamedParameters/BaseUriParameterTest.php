<?php
namespace Raml\Test\NamedParameters;

class BaseUriParameterTest extends \PHPUnit_Framework_TestCase
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


    /**
     * Returns a valid api def
     *
     * @throws \Exception
     *
     * @return \Raml\ApiDefinition
     */
    private function getValidDef()
    {
        $raml = <<<RAML
#%RAML 0.8
title: Users API
version: 1.2
baseUri: https://{apiDomain}.someapi.com/{version}
/users:
  displayName: retrieve all users
  baseUriParameters:
    apiDomain:
      enum: [ "api" ]
  /{userId}/image:
    displayName: access users pictures
    baseUriParameters:
      apiDomain:
        enum: [ "static" ]
    get:
      displayName: retrieve a user's picture
    put:
      displayName: update a user's picture
      baseUriParameters:
        apiDomain:
          enum: [ "content-update" ]
RAML;

        return $this->parser->parseFromString($raml, '');
    }

    // ---

    /**
     * @test
     */
    public function shouldCorrectlySubstituteTheVersion()
    {
        $apiDef = $this->getValidDef();

        $this->assertEquals('https://{apiDomain}.someapi.com/1.2', $apiDef->getBaseUrl());
    }

    /** @test */
    public function shouldCorrectlyParseBaseUriParameters()
    {
        $apiDef = $this->getValidDef();

        $resource = $apiDef->getResourceByUri('/users');
        $baseUriParameters = $resource->getBaseUriParameters();

        $this->assertEquals(1, count($baseUriParameters));
        $this->assertEquals('apiDomain', array_keys($baseUriParameters)[0]);
        $this->assertEquals('string', $baseUriParameters['apiDomain']->getType());
        $this->assertEquals(['api'], $baseUriParameters['apiDomain']->getEnum());
        $this->assertTrue($baseUriParameters['apiDomain']->isRequired());
    }

    /** @test */
    public function shouldOverrideBaseUriParametersInResource()
    {
        $apiDef = $this->getValidDef();
        $resource = $apiDef->getResourceByUri('/users/1/image');
        $this->assertEquals(['static'], $resource->getBaseUriParameters()['apiDomain']->getEnum());
    }

    /** @test */
    public function shouldOverrideBaseUriParametersInMethod()
    {
        $apiDef = $this->getValidDef();
        $resource = $apiDef->getResourceByUri('/users/1/image');
        $method = $resource->getMethod('PUT');
        $this->assertEquals(['content-update'], $method->getBaseUriParameters()['apiDomain']->getEnum());
    }
}
