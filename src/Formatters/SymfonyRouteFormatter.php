<?php

namespace Raml\Formatters;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class SymfonyRouteFormatter implements RouteFormatterInterface
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var boolean
     */
    private $addTrailingSlash = true;

    /**
     * The constructor accepts a RouteCollection, and a boolean flag to
     * append a trailing slash to the final routes or not.
     *
     * @param RouteCollection $routes
     * @param boolean $addTrailingSlash
     *  By default this is true
     */
    public function __construct(RouteCollection $routes, $addTrailingSlash = true)
    {
        $this->routes = $routes;
        $this->addTrailingSlash = $addTrailingSlash;
    }

    /**
     * {inheritDoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Given an array of RAML\Resources, this function will add each Resource
     * into the Symfony Route Collection, and set the corresponding method.
     *
     * @param array $resources
     *  Associative array where the key is the method and full path, and the value contains
     *  the path, method type (GET/POST etc.) and then the Raml\Method object
     * @return array
     */
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
