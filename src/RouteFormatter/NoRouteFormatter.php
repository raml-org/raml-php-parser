<?php

namespace Raml\RouteFormatter;

class NoRouteFormatter implements RouteFormatterInterface
{
    /**
     * @var array
     */
    private $routes;

    // ---
    // RouteFormatterInterface

    /**
     * Format an array of basic routes into an array of arrays
     *
     * @param BasicRoute[] $resources
     *
     * @return BasicRoute[]
     */
    public function format(array $resources)
    {
        $this->routes = [];

        foreach ($resources as $resource) {
            $this->routes[$resource->getType() .' ' . $resource->getUri()] = [
                'path' => $resource->getUri(),
                'type' => $resource->getType(),
                'method' => $resource->getMethod()
            ];
        }

        return $resources;
    }

    /**
     * Get the routes
     *
     * @return array [[
     *  path:   string
     *  type:   string
     *  method: \Raml\Method
     * ]]
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
