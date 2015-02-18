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
            // This is the path from the RAML, with or without a /.
            $path = $resource['path'] . ($this->addTrailingSlash ? '/' : '');

            // This is the baseUri + path, the complete URL.
            $url = $resource['baseUri'] . $path;

            // Now remove the host away, so we have the FULL path to the resource.
            // baseUri may also contain path that has been omitted for brevity in the
            // RAML creation.
            $host = parse_url($url, PHP_URL_HOST);
            $fullPath = substr($url, strpos($url, $host) + strlen($host));

            // Now build our Route class.

            $route = new Route($fullPath);
            $route->setMethods($resource['method']);
            $route->setSchemes($resource['protocols']);

            $this->routes->add($resource['method'] . ' ' . $path, $route);
        }

        return $resources;
    }
}
