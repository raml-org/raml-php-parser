<?php

class ApiDefinitionTest extends PHPUnit_Framework_TestCase
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

    /** @test */
    public function shouldReturnFullResourcesForRamlFileWithDefaultFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');
        $routes = $api->getResourcesAsUri();

        $this->assertCount(4, $routes->getRoutes());
        $this->assertEquals([
                'GET /songs',
                'POST /songs/{songId}',
                'GET /songs/{songId}',
                'DELETE /songs/{songId}'
            ], array_keys($routes->getRoutes()));
    }

    /** @test */
    public function shouldReturnFullResourcesForRamlFileWithNoFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $noFormatter = new Raml\RouteFormatter\NoRouteFormatter();
        $routes = $api->getResourcesAsUri($noFormatter, $api->getResources());

        $this->assertCount(4, $routes->getRoutes());
        $this->assertEquals([
            'GET /songs',
            'POST /songs/{songId}',
            'GET /songs/{songId}',
            'DELETE /songs/{songId}'
        ], array_keys($routes->getRoutes()));
    }

    /** @test */
    public function shouldReturnFullResourcesForRamlFileWithSymfonyFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $routeCollection= new Symfony\Component\Routing\RouteCollection();
        $routeFormatter = new Raml\RouteFormatter\SymfonyRouteFormatter($routeCollection);
        $routes = $api->getResourcesAsUri($routeFormatter, $api->getResources());

        $this->assertEquals($routeFormatter, $routes);

        $this->assertCount(4, $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\RouteCollection', $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\Route', $routeFormatter->getRoutes()->get('GET /songs/'));
        $this->assertEquals(['http'], $routeFormatter->getRoutes()->get('GET /songs/')->getSchemes());
    }

    /** @test */
    public function shouldReturnURIProtocol()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/protocols/noProtocolSpecified.raml');
        $this->assertCount(1, $api->getProtocols());
        $this->assertSame(array(
            'HTTP',
        ), $api->getProtocols());
    }

    /** @test */
    public function shouldProcessTypes()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/types.raml');
        $this->assertCount(1, $api->getTypes());
        $this->assertSame(array(
            'User' => array(
                'type' => 'object',
                'properties' => array(
                    'firstname' => 'string',
                    'lastname' => 'string',
                    'age' => 'number',
                )
            )
        ), $api->getTypes()->toArray());
    }

    /** @test */
    public function shouldParseTypesToSubTypes()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/raml-1.0/types.raml');
        $types = $api->getTypes();
        $object = $types->current();
        $this->assertInstanceOf('\Raml\Types\ObjectType', $object);
        $this->assertInstanceOf('\Raml\Types\IntegerType', $object->getPropertyByName('id'));
        $this->assertInstanceOf('\Raml\Types\StringType', $object->getPropertyByName('name'));
    }

    /** @test */
    public function shouldParseComplexTypes()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/raml-1.0/complexTypes.raml');
        // check types
        $org = $api->getTypes()->getTypeByName('Org');
        $this->assertInstanceOf('\Raml\Types\ObjectType', $org);
        // property will return a proxy object so to compare to actual type we will need to ask for the resolved object
        $this->assertInstanceOf('\Raml\Types\UnionType', $org->getPropertyByName('onCall')->getResolvedObject());
        $head = $org->getPropertyByName('Head');
        $this->assertInstanceOf('\Raml\Types\ObjectType', $head->getResolvedObject());
        $this->assertInstanceOf('\Raml\Types\StringType', $head->getPropertyByName('firstname'));
        $this->assertInstanceOf('\Raml\Types\StringType', $head->getPropertyByName('lastname'));
        $this->assertInstanceOf('\Raml\Types\StringType', $head->getPropertyByName('title'));
        $this->assertInstanceOf('\Raml\Types\StringType', $head->getPropertyByName('kind'));
        $reports = $head->getPropertyByName('reports');
        $this->assertInstanceOf('\Raml\Types\ArrayType', $reports);
        $phone = $head->getPropertyByName('phone')->getResolvedObject();
        $this->assertInstanceOf('\Raml\Types\StringType', $phone);
        // check resources
        $type = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType('application/json')->getType();
        $this->assertInstanceOf('\Raml\Types\ObjectType', $type->getResolvedObject());
    }

    /** @test */
    public function shouldPassValidResponse()
    {
        $api = $this->parser->parse(__DIR__ . '/fixture/raml-1.0/complexTypes.raml');
        $body = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType(
            'application/json'
        );
        /* @var $body \Raml\Body */

        $validResponse = '{
            "onCall": {
                "firstname": "nico",
                "lastname": "ark",
                "kind": "AlertableAdmin",
                "clearanceLevel": "low",
                "phone": "12321"
            },
            "Head": {
                "firstname": "nico",
                "lastname": "ark",
                "kind": "Manager",
                "reports": [
                    {
                        "firstname": "nico",
                        "lastname": "ark",
                        "kind": "Admin",
                        "clearanceLevel": "low"
                    }
                ],
                "phone": "123-23"
            }
        }';
        $type = $body->getType();
        $this->assertTrue($type->validate(json_decode($validResponse, true)));
    }

    /** @test */
    public function shouldRejectMissingParameterResponse()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/raml-1.0/complexTypes.raml');
        $body = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType('application/json');
        /* @var $body \Raml\Body */
        $type = $body->getType();

        $invalidResponse = [
            'onCall' => 'this is not an object',
            'Head' => 'this is not an object'
        ];

        $this->setExpectedException('\Raml\Exception\MissingRequiredPropertyException');
        $type->validate($invalidResponse);
    }

    /** @test */
    public function shouldReturnProtocolsIfSpecified()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/protocols/protocolsSpecified.raml');
        $this->assertCount(2, $api->getProtocols());
        $this->assertSame(array(
            'HTTP',
            'HTTPS'
        ), $api->getProtocols());
    }

    /** @test */
    public function shouldThrowInvalidProtocolExceptionIfWrongProtocol()
    {
        $this->setExpectedException('Raml\Exception\BadParameter\InvalidProtocolException');

        $this->parser->parse(__DIR__.'/fixture/protocols/invalidProtocolsSpecified.raml');
    }
}
