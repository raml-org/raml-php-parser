<?php

namespace Raml\RouteFormatter;

use \Raml\Method;

class BasicRoute
{
    /**
     * The base URL for the route
     *
     * @var string
     */
    private $baseUrl;

    /**
     * The full uri of the route
     *
     * @var string
     */
    private $uri;

    /**
     * The protocol(s) of the routes
     *
     * @var array
     */
    private $protocols;

    /**
     * The verb of the Route
     * [GET, POST, PUT, PATCH, DELETE]
     *
     * @var string
     */
    private $type;

    /**
     * Any uri parameters of the route.
     *
     * @var array
     */
    private $uriParameters;

    /**
     * The Method definition
     *
     * @var Method
     */
    private $method;

    // ---

    /**
     * Create a new basic route
     *
     * @param string $baseUrl
     * @param string $uri
     * @param array $protocols
     * @param string $type
     * @param array $uriParameters
     * @param Method $method
     */
    public function __construct($baseUrl, $uri, $protocols, $type, $uriParameters, Method $method)
    {
        $this->baseUrl = $baseUrl;
        $this->uri = $uri;
        $this->protocols = $protocols;
        $this->type = $type;
        $this->uriParameters = $uriParameters;
        $this->method = $method;
    }

    // --

    /**
     * Gets the base URL of the route
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get the URI of the route
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Gets the Protocols of the route
     *
     * @return array
     */
    public function getProtocols()
    {
        return $this->protocols;
    }

    /**
     * Get the method string of the route
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the URI parameters of the route
     *
     * @return array
     */
    public function getUriParameters()
    {
        return $this->uriParameters;
    }

    /**
     * Get the Method definition
     *
     * @return Method
     */
    public function getMethod()
    {
        return $this->method;
    }
}
