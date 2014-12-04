<?php

class MethodTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldGetTheTypeInUpperCase()
    {
        $method = new \Raml\Method('get', []);
        $this->assertSame('GET', $method->getType());

        $method = new \Raml\Method('Post', []);
        $this->assertSame('POST', $method->getType());
    }

    /** @test */
    public function shouldGetTheDescriptionIfPassedInTheDataArray()
    {
        $method = new \Raml\Method('get', ['description' => 'A dummy description']);
        $this->assertSame('A dummy description', $method->getDescription());

        $method = new \Raml\Method('get', []);
        $this->assertSame('', $method->getDescription());
    }

    /** @test */
    public function shouldGetNullForResponseIfNoneIsExists()
    {
        $method = new \Raml\Method('get', []);
        $this->assertSame(null, $method->getResponses());
        $this->assertSame(null, $method->getResponse(200));
    }

    /** @test */
    public function shouldGetNullForResponseIfNotAnArrayIsPassed()
    {
        $method = new \Raml\Method('get', ['responses' => 'invalid']);
        $this->assertSame(null, $method->getResponses());
        $this->assertSame(null, $method->getResponse(200));
    }

    /** @test */
    public function shouldGetValidResponsesIfPassedExpectedValues()
    {
        $method = new \Raml\Method('get', [
            'description' => 'A dummy method',
            'responses' => [
                200 => [
                    'body' => [
                        'text/xml' => 'xml body',
                        'text/txt' => 'plain text'
                    ],
                    'description' => 'A dummy response',
                    'headers' => []
                ]
            ]
        ]);
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
    public function shouldGetNullForQueryParametersIfNoneIsExists()
    {
        $method = new \Raml\Method('get', []);
        $this->assertSame(null, $method->getQueryParameters());
    }
}