<?php

namespace Raml\RouteFormatter;

use \Raml\Method;

class BasicRoute
{
    /**
     * The full uri of the route
     *
     * @var string
     */
    private $uri;

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
    public function __construct($uri, $type, Method $method)
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->type = $type;
    }

    // --

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
