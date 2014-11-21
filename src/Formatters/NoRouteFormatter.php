<?php

namespace Raml\Formatters;

class NoRouteFormatter implements RouteFormatterInterface
{
    /**
     * @var array
     */
    private $routes;

    /**
     * {inheritDoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * {inheritDoc}
     */
    public function format(array $resources)
    {
        $this->routes = $resources;

        return $resources;
    }
}
