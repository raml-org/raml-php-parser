<?php

namespace Raml\Formatters;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class SymfonyRouteFormatter implements RouteFormatterInterface
{
    private $routes;
    private $addTrailingSlash = true;

    public function __construct(RouteCollection $routes, $addTrailingSlash = true)
    {
        $this->routes = $routes;
        $this->addTrailingSlash = $addTrailingSlash;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function format(array $resources)
    {
        foreach ($resources as $path => $resource) {
            $path = ($this->addTrailingSlash) ? $path . '/' : $path;

            $route = new Route($path);
            $route->setMethods($resource['method']);

            $this->routes->add($path, $route);
        }

        return $resources;
    }
}