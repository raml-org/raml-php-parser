<?php

class MethodTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldGetTheTypeInUpperCase()
    {
        $apiDefinition = new \Raml\ApiDefinition('The title');

        $method = \Raml\Method::createFromArray('get', [], $apiDefinition);
        $this->assertSame('GET', $method->getType());

        $method = \Raml\Method::createFromArray('Post', [], $apiDefinition);
        $this->assertSame('POST', $method->getType());
    }

    /** @test */
    public function shouldGetTheDescriptionIfPassedInTheDataArray()
    {
        $apiDefinition = new \Raml\ApiDefinition('The title');

        $method = \Raml\Method::createFromArray('get', ['description' => 'A dummy description'], $apiDefinition);
        $this->assertSame('A dummy description', $method->getDescription());

        $method = \Raml\Method::createFromArray('get', [], $apiDefinition);
        $this->assertNull($method->getDescription());
    }

    /** @test */
    public function shouldGetNullForResponseIfNoneIsExists()
    {
        $apiDefinition = new \Raml\ApiDefinition('The title');

        $method = \Raml\Method::createFromArray('get', [], $apiDefinition);
        $this->assertSame([], $method->getResponses());
        $this->assertSame(null, $method->getResponse(200));
    }

    /** @test */
    public function shouldGetNullForResponseIfNotAnArrayIsPassed()
    {
        $apiDefinition = new \Raml\ApiDefinition('The title');

        $method = \Raml\Method::createFromArray('get', ['responses' => 'invalid'], $apiDefinition);
        $this->assertSame([], $method->getResponses());
        $this->assertSame(null, $method->getResponse(200));
    }

    /** @test */
    public function shouldGetValidResponsesIfPassedExpectedValues()
    {
        $apiDefinition = new \Raml\ApiDefinition('The title');

        $method = \Raml\Method::createFromArray(
            'get',
            [
                'description' => 'A dummy method',
                'responses' => [
                    200 => [
                        'body' => [
                            'text/xml' => ['description' => 'xml body'],
                            'text/txt' => ['description' => 'plain text']
                        ],
                        'description' => 'A dummy response',
                        'headers' => []
                    ]
                ]
            ],
            $apiDefinition
        );

        $this->assertInternalType('array', $method->getResponses());
        $this->assertCount(1, $method->getResponses());

        $responses =  $method->getResponses();
        $this->assertInstanceOf('\Raml\Response', array_values($responses)[0]);
        $this->assertInstanceOf('\Raml\Response', $method->getResponse(200));
        $this->assertSame(null, $method->getResponse(400));

        $this->assertSame('A dummy method', $method->getDescription());
        $this->assertSame('A dummy response', array_values($responses)[0]->getDescription());
    }

    /** @test */
    public function shouldGetEmptyArrayForQueryParametersIfNoneIsExists()
    {
        $apiDefinition = new \Raml\ApiDefinition('The title');
        $method = \Raml\Method::createFromArray('get', [], $apiDefinition);
        $this->assertEquals([], $method->getQueryParameters());
    }
}
