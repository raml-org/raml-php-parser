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

        $noFormatter = new Raml\Formatters\NoRouteFormatter();
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
        $routeFormatter = new Raml\Formatters\SymfonyRouteFormatter($routeCollection);
        $routes = $api->getResourcesAsUri($routeFormatter, $api->getResources());

        $this->assertEquals($routeFormatter, $routes);

        $this->assertCount(4, $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\RouteCollection', $routeFormatter->getRoutes());
        $this->assertInstanceOf('\Symfony\Component\Routing\Route', $routeFormatter->getRoutes()->get('GET /songs/'));
        $this->assertEquals('http://example.api.com/v1', $routeFormatter->getRoutes()->get('GET /songs/')->getHost());
    }
}
