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
        if (isset($data['responses']) && is_array($data['responses'])) {
            foreach ($data['responses'] as $responseCode => $responseData) {
                $this->responses[$responseCode] = new Response(
                    $responseCode,
                    isset($responseData['body']) ? $responseData['body'] : [],
                    isset($responseData['description']) ? $responseData['description'] : null,
                    isset($responseData['headers']) ? $responseData['headers'] : []
                );
            }
        }

        if (isset($data['queryParameters']) && is_array($data['queryParameters'])) {
            foreach ($data['queryParameters'] as $name => $queryParameterData) {
                $this->queryParameters[$name] = new QueryParameter(
                    isset($queryParameterData['description']) ? $queryParameterData['description'] : null,
                    isset($queryParameterData['type']) ? $queryParameterData['type'] : 'string'
                );
            }
        }

        $this->type = strtoupper($type);
        $this->body = isset($data['body']) ? $data['body'] : [];
        $this->headers = isset($data['headers']) ? $data['headers'] : [];
        $this->description = isset($data['description']) ? $data['description'] : '';
    }

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
}
