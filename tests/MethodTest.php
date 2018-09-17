<?php

namespace Raml\Tests;

use PHPUnit\Framework\TestCase;
use Raml\ApiDefinition;
use Raml\Method;
use Raml\Parser;
use Raml\Response;

class MethodTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetTheTypeInUpperCase()
    {
        $apiDefinition = new ApiDefinition('The title');

        $method = Method::createFromArray('get', [], $apiDefinition);
        $this->assertSame('GET', $method->getType());

        $method = Method::createFromArray('Post', [], $apiDefinition);
        $this->assertSame('POST', $method->getType());

        $method = Method::createFromArray('options', [], $apiDefinition);
        $this->assertSame('OPTIONS', $method->getType());
    }

    /**
     * @test
     */
    public function shouldGetTheDescriptionIfPassedInTheDataArray()
    {
        $apiDefinition = new ApiDefinition('The title');

        $method = Method::createFromArray('get', ['description' => 'A dummy description'], $apiDefinition);
        $this->assertSame('A dummy description', $method->getDescription());

        $method = Method::createFromArray('get', [], $apiDefinition);
        $this->assertNull($method->getDescription());
    }

    /**
     * @test
     */
    public function shouldGetNullForResponseIfNoneIsExists()
    {
        $apiDefinition = new ApiDefinition('The title');

        $method = Method::createFromArray('get', [], $apiDefinition);
        $this->assertSame([], $method->getResponses());
        $this->assertSame(null, $method->getResponse(200));
    }

    /**
     * @test
     */
    public function shouldGetNullForResponseIfNotAnArrayIsPassed()
    {
        $apiDefinition = new ApiDefinition('The title');

        $method = Method::createFromArray('get', ['responses' => 'invalid'], $apiDefinition);
        $this->assertSame([], $method->getResponses());
        $this->assertSame(null, $method->getResponse(200));
    }

    /**
     * @test
     */
    public function shouldGetValidResponsesIfPassedExpectedValues()
    {
        $apiDefinition = new ApiDefinition('The title');

        $method = Method::createFromArray(
            'get',
            [
                'description' => 'A dummy method',
                'responses' => [
                    200 => [
                        'body' => [
                            'text/xml' => ['description' => 'xml body'],
                            'text/txt' => ['description' => 'plain text'],
                        ],
                        'description' => 'A dummy response',
                        'headers' => [],
                    ]
                ]
            ],
            $apiDefinition
        );

        $this->assertInternalType('array', $method->getResponses());
        $this->assertCount(1, $method->getResponses());

        $responses = $method->getResponses();
        $this->assertInstanceOf(Response::class, array_values($responses)[0]);
        $this->assertInstanceOf(Response::class, $method->getResponse(200));
        $this->assertSame(null, $method->getResponse(400));

        $this->assertSame('A dummy method', $method->getDescription());
        $this->assertSame('A dummy response', array_values($responses)[0]->getDescription());
    }

    /**
     * @test
     */
    public function shouldGetEmptyArrayForQueryParametersIfNoneIsExists()
    {
        $apiDefinition = new ApiDefinition('The title');
        $method = Method::createFromArray('get', [], $apiDefinition);
        $this->assertEquals([], $method->getQueryParameters());
    }

    /**
     * @test
     */
    public function shouldGetGlobalProtocols()
    {
        $parser = new Parser();
        $apiDefinition = $parser->parse(__DIR__ . '/fixture/protocols/noProtocolSpecified.raml');

        $method = Method::createFromArray(
            'get',
            [
                'description' => 'A dummy method',
            ],
            $apiDefinition
        );

        $this->assertInternalType('array', $method->getProtocols());
        $this->assertCount(1, $method->getProtocols());
        $this->assertSame(['HTTP'], $method->getProtocols());
    }

    /**
     * @test
     */
    public function shouldGetOverrideProtocols()
    {
        $parser = new Parser();
        $apiDefinition = $parser->parse(__DIR__ . '/fixture/protocols/noProtocolSpecified.raml');

        $method = Method::createFromArray(
            'get',
            [
                'description' => 'A dummy method',
                'protocols' => ['HTTP', 'HTTPS'],
            ],
            $apiDefinition
        );

        $this->assertInternalType('array', $method->getProtocols());
        $this->assertCount(2, $method->getProtocols());
        $this->assertSame([
            'HTTP',
            'HTTPS',
        ], $method->getProtocols());
    }
}
