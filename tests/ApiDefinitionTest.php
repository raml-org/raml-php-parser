<?php

namespace Raml\Tests;

use PHPUnit\Framework\TestCase;
use Raml\Body;
use Raml\Exception\BadParameter\InvalidProtocolException;
use Raml\ParseConfiguration;
use Raml\Parser;
use Raml\RouteFormatter\NoRouteFormatter;
use Raml\RouteFormatter\SymfonyRouteFormatter;
use Raml\Types\ArrayType;
use Raml\Types\IntegerType;
use Raml\Types\LazyProxyType;
use Raml\Types\ObjectType;
use Raml\Types\StringType;
use Raml\Types\TypeValidationError;
use Raml\Types\UnionType;
use Raml\ValidatorInterface;
use Symfony\Component\Routing\RouteCollection;

class ApiDefinitionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        setlocale(LC_NUMERIC, 'C');
    }

    /**
     * @return Parser
     */
    private function buildParser()
    {
        return new Parser();
    }

    /**
     * @test
     */
    public function shouldReturnFullResourcesForRamlFileWithDefaultFormatter()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/includeSchema.raml');
        $routes = $api->getResourcesAsUri();

        $this->assertCount(4, $routes->getRoutes());
        $this->assertEquals([
            'GET /songs',
            'POST /songs/{songId}',
            'GET /songs/{songId}',
            'DELETE /songs/{songId}',
        ], array_keys($routes->getRoutes()));
    }

    /**
     * @test
     */
    public function shouldReturnFullResourcesForRamlFileWithNoFormatter()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/includeSchema.raml');

        $noFormatter = new NoRouteFormatter();
        $routes = $api->getResourcesAsUri($noFormatter, $api->getResources());

        $this->assertCount(4, $routes->getRoutes());
        $this->assertEquals([
            'GET /songs',
            'POST /songs/{songId}',
            'GET /songs/{songId}',
            'DELETE /songs/{songId}',
        ], array_keys($routes->getRoutes()));
    }

    /**
     * @test
     */
    public function shouldReturnFullResourcesForRamlFileWithSymfonyFormatter()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/includeSchema.raml');

        $routeCollection = new RouteCollection();
        $routeFormatter = new SymfonyRouteFormatter($routeCollection);
        $routes = $api->getResourcesAsUri($routeFormatter, $api->getResources());

        $this->assertEquals($routeFormatter, $routes);

        $this->assertCount(4, $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\RouteCollection', $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\Route', $routeFormatter->getRoutes()->get('GET /songs/'));
        $this->assertEquals(['http'], $routeFormatter->getRoutes()->get('GET /songs/')->getSchemes());
    }

    /**
     * @test
     */
    public function shouldReturnURIProtocol()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/protocols/noProtocolSpecified.raml');
        $this->assertCount(1, $api->getProtocols());
        $this->assertSame([
            'HTTP',
        ], $api->getProtocols());
    }

    /**
     * @test
     */
    public function shouldProcessTypes()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/types.raml');
        $this->assertCount(1, $api->getTypes());
        $this->assertSame([
            'User' => [
                'type' => 'object',
                'properties' => [
                    'firstname' => 'string',
                    'lastname' => 'string',
                    'age' => 'number',
                ],
            ],
        ], $api->getTypes()->toArray());
    }

    /**
     * @test
     */
    public function shouldBeAbleToAccessOriginalInheritanceTypes()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/inheritanceTypes.raml');
        /** @var LazyProxyType $adminType */
        $adminType = $api->getTypes()->getTypeByName('Admin');
        $this->assertInstanceOf(LazyProxyType::class, $adminType);
        $this->assertCount(1, $adminType->getProperties());
        foreach ($adminType->getProperties() as $property) {
            if ($property->getName() === 'clearanceLevel') {
                $this->assertTrue($property->getRequired());
            }
        }
        $parent = $adminType->getParent();
        $this->assertNotNull($parent);
        $this->assertCount(4, $parent->getProperties());

        /** @var LazyProxyType $managerType */
        $managerType = $api->getTypes()->getTypeByName('Manager');
        foreach ($managerType->getProperties() as $property) {
            if ($property->getName() === 'clearanceLevel') {
                $this->assertFalse($property->getRequired());
            }
        }
    }

    /**
     * @test
     */
    public function shouldParseLibraries()
    {
        $configuration = new ParseConfiguration();
        $configuration->allowRemoteFileInclusion();

        $parser = new Parser(null, null, null, $configuration);
        $api = $parser->parse(__DIR__ . '/fixture/raml-1.0/types.raml');
        /** @var ObjectType $fileType */
        $fileType = $api->getTypes()->getTypeByName('Library.File');
        /** @var ObjectType $folderType */
        $folderType = $api->getTypes()->getTypeByName('Library.Folder');

        $this->assertNotNull($fileType);
        $this->assertNotNull($folderType);

        /** @var ArrayType $property */
        $property = $folderType->getPropertyByName('files');
        $this->assertSame($fileType, $property->getItems());
    }

    /**
     * @test
     */
    public function shouldParseTypesToSubTypes()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/types.raml');
        $types = $api->getTypes();
        $object = $types->current();
        $this->assertInstanceOf(ObjectType::class, $object);
        $this->assertInstanceOf(IntegerType::class, $object->getPropertyByName('id'));
        $this->assertInstanceOf(StringType::class, $object->getPropertyByName('name'));
    }

    /**
     * @test
     */
    public function shouldParseComplexTypes()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/complexTypes.raml');
        // check types
        $org = $api->getTypes()->getTypeByName('Org');
        $this->assertInstanceOf(ObjectType::class, $org);
        // property will return a proxy object so to compare to actual type we will need to ask for the resolved object
        $this->assertInstanceOf(UnionType::class, $org->getPropertyByName('onCall')->getResolvedObject());
        $head = $org->getPropertyByName('Head');
        $this->assertInstanceOf(ObjectType::class, $head->getResolvedObject());
        $this->assertInstanceOf(StringType::class, $head->getPropertyByName('firstname'));
        $this->assertInstanceOf(StringType::class, $head->getPropertyByName('lastname'));
        $this->assertInstanceOf(StringType::class, $head->getPropertyByName('title'));
        $this->assertInstanceOf(StringType::class, $head->getPropertyByName('kind'));
        $reports = $head->getPropertyByName('reports');
        $this->assertInstanceOf(ArrayType::class, $reports);
        $phone = $head->getPropertyByName('phone')->getResolvedObject();
        $this->assertInstanceOf(StringType::class, $phone);
        // check resources
        $type = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType('application/json')->getType();
        $this->assertInstanceOf(ObjectType::class, $type->getResolvedObject());
    }

    /**
     * @test
     */
    public function shouldPassValidResponse()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/complexTypes.raml');
        $body = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType(
            'application/json'
        );
        /** @var Body $body */
        $validResponse = '{
            "onCall": {
                "firstname": "John",
                "lastname": "Flare",
                "age": 41,
                "kind": "AlertableAdmin",
                "clearanceLevel": "low",
                "phone": "12321"
            },
            "Head": {
                "firstname": "Nico",
                "lastname": "Ark",
                "age": 41,
                "kind": "Manager",
                "reports": [
                    {
                        "firstname": "Archie",
                        "lastname": "Ark",
                        "age": 40,
                        "kind": "Admin"
                    }
                ],
                "phone": "123-23"
            }
        }';
        $type = $body->getType();
        $type->validate(json_decode($validResponse, true));
        $this->assertTrue($type->isValid(), sprintf('Validation failed with following errors: %s', implode(', ', array_map('strval', $type->getErrors()))));
    }

    /**
     * @test
     */
    public function shouldRejectMissingParameterResponse()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/complexTypes.raml');
        $body = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType('application/json');
        /** @var Body $body */
        $type = $body->getType();

        $invalidResponse = [
            'onCall' => 'this is not an object',
            'Head' => 'this is not a Head object'
        ];

        $type->validate($invalidResponse);
        $this->assertValidationFailedWithErrors(
            $type,
            [
                new TypeValidationError(
                    'Alertable',
                    'Value did not pass validation against any type: '
                        . 'Manager (Manager (Expected object, got (string) "this is not an object")), '
                        . 'AlertableAdmin (AlertableAdmin (Expected object, got (string) "this is not an object"))'
                ),
                new TypeValidationError('Head', 'Expected object, got (string) "this is not a Head object"'),
            ]
        );
    }

    /**
     * @test
     */
    public function shouldRejectInvalidIntegerParameterResponse()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/complexTypes.raml');
        $body = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType('application/json');
        /** @var Body $body */
        $type = $body->getType();

        $invalidResponse = [
            'onCall' => [
                'firstname' => 'John',
                'lastname' => 'Flare',
                'age' => 18.5,
                'kind' => 'AlertableAdmin',
                'clearanceLevel' => 'low',
                'phone' => '12321',
            ],
            'Head' => [
                'firstname' => 'Nico',
                'lastname' => 'Ark',
                'age' => 71,
                'kind' => 'Manager',
                'reports' => [
                    [
                        'firstname' => 'Archie',
                        'lastname' => 'Ark',
                        'kind' => 'Admin',
                        'age' => 17,
                        'clearanceLevel' => 'low',
                    ],
                ],
                'phone' => '123-23',
            ],
        ];

        $type->validate($invalidResponse);
        $this->assertValidationFailedWithErrors(
            $type,
            [
                new TypeValidationError('age', 'Maximum allowed value: 70, got 71'),
                new TypeValidationError('age', 'Minimum allowed value: 18, got 17'),
                new TypeValidationError(
                    'Alertable',
                    'Value did not pass validation against any type: '
                        . 'AlertableAdmin (age (Expected int, got (double) "18.5"))'
                ),
            ]
        );
    }

    /**
     * @test
     */
    public function shouldRejectInvalidStringParameterResponse()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/complexTypes.raml');
        $body = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType('application/json');
        /* @var $body Body */
        $type = $body->getType();

        $invalidResponse = [
            'onCall' => [
                'firstname' => 'John',
                'lastname' => 'F',
                'age' => 30,
                'kind' => 'AlertableAdmin',
                'clearanceLevel' => 'low',
                'phone' => '12321',
            ],
            'Head' => [
                'firstname' => 'Nico von Teufelspieler, the true duke of northern Blasphomores',
                'lastname' => 'Ark',
                'age' => 30,
                'kind' => 'Manager',
                'reports' => [
                    [
                        'firstname' => 'Archie',
                        'lastname' => 'Ark',
                        'kind' => 'Admin',
                        'age' => 30,
                        'clearanceLevel' => 'low',
                    ],
                ],
                'phone' => '123-23 33 22',
            ],
        ];

        $type->validate($invalidResponse);
        $this->assertValidationFailedWithErrors(
            $type,
            [
                new TypeValidationError('firstname', 'Maximum allowed length: 50, got 62'),
                new TypeValidationError('Phone', 'String "123-23 33 22" did not match pattern /^[0-9|-]+$/'),
                new TypeValidationError(
                    'Alertable',
                    'Value did not pass validation against any type: '
                        . 'AlertableAdmin (lastname (Minimum allowed length: 2, got 1))'
                ),
            ]
        );
    }

    /**
     * @test
     */
    public function shouldRejectInvalidEnumParameterResponse()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/raml-1.0/complexTypes.raml');
        $body = $api->getResourceByPath('/orgs/{orgId}')->getMethod('get')->getResponse(200)->getBodyByType('application/json');
        /** @var Body $body */
        $type = $body->getType();

        $invalidResponse = [
            'onCall' => [
                'firstname' => 'John',
                'lastname' => 'Flare',
                'age' => 30,
                'kind' => 'AlertableAdmin',
                'clearanceLevel' => 'average',
                'phone' => '12321',
            ],
            'Head' => [
                'firstname' => 'Nico',
                'lastname' => 'Ark',
                'age' => 30,
                'kind' => 'Manager',
                'reports' => [
                    [
                        'firstname' => 'Archie',
                        'lastname' => 'Ark',
                        'kind' => 'Admin',
                        'age' => 30,
                    ],
                ],
                'phone' => '123-23',
            ],
        ];

        $type->validate($invalidResponse);
        $this->assertValidationFailedWithErrors(
            $type,
            [
                new TypeValidationError(
                    'Alertable',
                    'Value did not pass validation against any type: '
                        . 'AlertableAdmin (ClearanceLevels (Expected any of [low, high], got (string) "average"))'
                ),
            ]
        );
    }

    /**
     * @test
     */
    public function shouldReturnProtocolsIfSpecified()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/protocols/protocolsSpecified.raml');
        $this->assertCount(2, $api->getProtocols());
        $this->assertSame([
            'HTTP',
            'HTTPS',
        ], $api->getProtocols());
    }

    /**
     * @test
     */
    public function shouldThrowInvalidProtocolExceptionIfWrongProtocol()
    {
        $this->expectException(InvalidProtocolException::class);

        $this->buildParser()->parse(__DIR__ . '/fixture/protocols/invalidProtocolsSpecified.raml');
    }

    /**
     * @param TypeValidationError[] $errors
     */
    private function assertValidationFailedWithErrors(ValidatorInterface $validator, $errors)
    {
        $this->assertFalse($validator->isValid(), 'Validator expected to fail');
        foreach ($errors as $error) {
            $this->assertContains(
                $error,
                $validator->getErrors(),
                $message = sprintf('Validator expected to contain error: %s', $error->__toString()),
                $ignoreCase = false,
                $checkForObjectIdentity = false
            );
        }
    }

    /**
     * @test
     */
    public function shouldReturnFullResourcesNameForRamlFileWithUrlPrefix()
    {
        $api = $this->buildParser()->parse(__DIR__ . '/fixture/includeUrlPrefix.raml');
        $this->assertEquals([
            '/prefix/songs',
        ], array_keys($api->getResources()));
    }
}
