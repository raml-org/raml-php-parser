<?php
namespace Raml;

class Response
{
    private $statusCode;

    private $description;

    private $body;

    private $headers;

    // ---

    /**
     * Create a new Resource from an array
     *
     * @param $data
     */
    public function __construct($statusCode, $body = [], $description = null, $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->description = $description;
        $this->headers = $headers;
    }

    // ---

    public function getSchemaByType($type)
    {
        return isset($this->body[$type]) ? $this->body[$type]['schema'] : null;
    }
}
