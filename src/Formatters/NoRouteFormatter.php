<?php

namespace Raml\Formatters;

class NoRouteFormatter implements RouteFormatterInterface
{
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
        return $resources;
    }
}
