<?php

namespace Raml\RouteFormatter;

use \Raml\Method;

class BasicRoute
{
    private $baseUrl;

    /**
     * The full uri of the route
     *
     * @var string
     */
    private $uri;

    private $protocols;

    /**
     * The verb of the Route
     * [GET, POST, PUT, PATCH, DELETE]
     *
     * @var string
     */
    private $type;

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
     * @param string $uri
     * @param string $type
     * @param Method $method
     */
    public function __construct($baseUrl, $uri, $protocols, $type, Method $method)
    {
        $this->baseUrl = $baseUrl;
        $this->uri = $uri;
        $this->protocols = $protocols;
        $this->method = $method;
        $this->type = $type;
    }

    // --

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
     * Get the Method definition
     *
     * @return Method
     */
    public function getMethod()
    {
        return $this->method;
    }
}
