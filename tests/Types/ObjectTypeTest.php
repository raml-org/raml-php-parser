<?php

namespace Raml\Tests\Types;

use PHPUnit\Framework\TestCase;
use Raml\ApiDefinition;
use Raml\Parser;
use Raml\Types\TypeValidationError;

class ObjectTypeTest extends TestCase
{
    /**
     * @var ApiDefinition
     */
    private $apiDefinition;

    protected function setUp()
    {
        parent::setUp();
        $this->apiDefinition = (new Parser())->parse(__DIR__ . '/../fixture/object_types.raml');
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateCorrectType()
    {
        $resource = $this->apiDefinition->getResourceByUri('/actors/1');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate([
            'fistName' => 'Jackie',
            'lastName' => 'Сhan',
        ]);

        $this->assertTrue($type->isValid());
    }

    /**
     * @test
     */
    public function shouldNotCorrectlyValidateObjectType()
    {
        $resource = $this->apiDefinition->getResourceByUri('/actors/1');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('application/json');
        $type = $body->getType();

        $type->validate('Jackie Сhan');

        $this->assertFalse($type->isValid());
        $this->assertCount(1, $type->getErrors());
        $this->assertInstanceOf(TypeValidationError::class, $type->getErrors()[0]);
    }
}
