<?php

namespace Raml;

interface MessageSchemaInterface
{
    /**
     * Returns the headers
     *
     * @return NamedParameter[]
     */
    public function getHeaders();

    /**
     * Add a new header
     *
     * @param NamedParameter $header
     */
    public function addHeader(NamedParameter $header);

    /**
     * Get the body by type
     *
     * @param string $type
     *
     * @throws \Exception
     *
     * @return BodyInterface
     */
    public function getBodyByType($type);

    /**
     * Get an array of all bodies
     *
     * @return array The array of bodies
     */
    public function getBodies();

    /**
     * Add a body
     *
     * @param BodyInterface $body
     */
    public function addBody(BodyInterface $body);
}
