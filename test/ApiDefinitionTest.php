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
    public function shouldReturnFullResourcesForRamlFileWithNoFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $noFormatter = new Raml\Formatters\NoRouteFormatter();
        $routes = $api->getResourcesAsUri($noFormatter, $api->getResources());

        $this->assertCount(4, $routes);
        $this->assertCount(4, $noFormatter->getRoutes());
    }

    /** @test */
    public function shouldReturnFullResourcesForRamlFileWithSymfonyFormatter()
    {
        $api = $this->parser->parse(__DIR__.'/fixture/includeSchema.raml');

        $routeCollection= new Symfony\Component\Routing\RouteCollection();
        $routeFormatter = new Raml\Formatters\SymfonyRouteFormatter($routeCollection);
        $routes = $api->getResourcesAsUri($routeFormatter, $api->getResources());

        $this->assertCount(4, $routes);
        $this->assertCount(4, $routeFormatter->getRoutes());
    }
}
