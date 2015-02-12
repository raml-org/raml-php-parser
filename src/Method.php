<?php
namespace Raml;

class Method
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $queryParameters = null;

    /**
     * @var array
     */
    private $body;

    /**
     * @var array
     */
    private $responses = null;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * Create a new Method from an array
     *
     * @param $data
     */
    public function __construct($type, array $data)
    {
        $responses = $this->getArrayValue($data, 'responses', []);
        if (is_array($responses)) {
            foreach ($responses as $responseCode => $responseData) {
                $this->responses[$responseCode] = new Response(
                    $responseCode,
                    $this->getArrayValue($responseData, 'body') ?: [],
                    $this->getArrayValue($responseData, 'description'),
                    $this->getArrayValue($responseData, 'headers') ?: []
                );
            }
        }

        $queryParameters = $this->getArrayValue($data, 'queryParameters', []);
        if (is_array($queryParameters)) {
            foreach ($queryParameters as $name => $queryParameterData) {
                $this->queryParameters[$name] = new QueryParameter(
                    $this->getArrayValue($queryParameterData, 'description'),
                    $this->getArrayValue($queryParameterData, 'type', 'string'),
                    $this->getArrayValue($queryParameterData, 'displayName', $this->convertKeyToDisplayName($name)),
                    $this->getArrayValue($queryParameterData, 'example'),
                    $this->getArrayValue($queryParameterData, 'required', false)
                );
            }
        }

        $this->type = strtoupper($type);
        $this->body = $this->getArrayValue($data, 'body', []);
        $this->headers = $this->getArrayValue($data, 'headers', []);
        $this->description = $this->getArrayValue($data, 'description', '');
    }

    /**
     * Helper method to extract items from array
     *
     * @param array   $data
     * @param string  $key
     * @param boolean $required
     *
     * @throws \Exception
     *
     * @return null
     */
    private function getArrayValue($data, $key, $defaultValue = null)
    {
        return isset($data[$key]) ? $data[$key] : $defaultValue;
    }

    // --

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getSchemaByType($type)
    {
        return isset($this->body[$type]['schema']) ? $this->body[$type]['schema'] : null;
    }

    /**
     * Get example by type (application/json, text/plain, ...)
     *
     * @param string $type
     * @return array
     */
    public function getExampleByType($type)
    {
        return isset($this->body[$type]['example']) ? $this->body[$type]['example'] : null;
    }

    /**
     * Get a response by the response code (200, 404,....)
     *
     * @param integer $responseCode
     *
     * @return \Raml\Response
     */
    public function getResponse($responseCode)
    {
        return isset($this->responses[$responseCode]) ? $this->responses[$responseCode] : null;
    }

    /**
     * Gets the query parameters of this method
     *
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }

    /**
     * Gets description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    // ---

    /**
     * If a display name is not provided then we attempt to construct a decent one from the key.
     *
     * @param string $uri
     * @return string
     */
    private function convertKeyToDisplayName($uri)
    {
        $separators = ['-', '_'];
        $uriParts = explode('/', $uri);
        return ucwords(str_replace($separators, ' ', end($uriParts)));
    }
}
