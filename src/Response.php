<?php
namespace Raml;

class Response
{
    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @var array
     */
    private $body;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $headers;

    /**
     * Constructor for a new Response object
     *
     * @param integer $statusCode
     * @param array $body
     * @param string $description
     * @param array $headers
     */
    public function __construct($statusCode, $body = [], $description = null, $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->description = $description;
        $this->headers = $headers;
    }

    /**
     * Given a type (such as application/json), return the schema.
     *
     * @param string $type
     * @return array
     */
    public function getSchemaByType($type)
    {
        return isset($this->body[$type]['schema']) ? $this->body[$type]['schema'] : null;
    }

    /**
     * Given a type (such as application/json), return the example.
     *
     * @param string $type
     * @return array
     */
    public function getExampleByType($type)
    {
        return isset($this->body[$type]['example']) ? $this->body[$type]['example'] : null;
    }
}
