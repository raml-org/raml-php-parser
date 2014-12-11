<?php

namespace Raml\RouteFormatter;

interface RouteFormatterInterface
{
    /**
     * Accepts an array of Raml\Resource objects to apply formatting to. Note that
     * this array contains the full Resource path, such as /songs/{songId} rather than
     * just /songs and then /{songId}.
     *
     * @param array $resources
     *  Associative array where the key is the method and full path, and the value contains
     *  the path, method type (GET/POST etc.) and then the Raml\Method object
     *
     * @return array
     */
    public function format(array $resources);

    /**
     * Returns all the routes from this formatter.
     */
    public function getRoutes();
}
