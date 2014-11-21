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
    private $headers;

    /**
     * @var array
     */
    private $protocols;

    /**
     * @var array
     */
    private $queryParameters;

    /**
     * @var array
     */
    private $body;

    /**
     * @var array
     */
    private $responses;

    /**
     * Create a new Method from an array
     *
     * @param $data
     */
    public function __construct($type, $data)
    {
        if (isset($data['responses'])) {
            foreach ($data['responses'] as $responseCode => $responseData) {
                $this->responses[$responseCode] = new \Raml\Response(
                    $responseCode,
                    isset($responseData['body']) ? $responseData['body'] : [],
                    isset($responseData['description']) ? $responseData['description'] : null,
                    isset($responseData['headers']) ? $responseData['headers'] : []
                );
            }
        }

        if (isset($data['queryParameters'])) {
            foreach ($data['queryParameters'] as $name => $data) {
                $this->queryParameters[$name] = new \Raml\QueryParameter(
                    isset($data['description']) ? $data['description'] : null,
                    isset($data['type']) ? $data['type'] : 'string'
                );
            }
        }

        $this->type = strtoupper($type);
        $this->body = isset($data['body']) ? $data['body'] : [];
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
        return $this->responses[$responseCode];
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
}
