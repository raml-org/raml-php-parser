<?php
namespace Raml;

class Method
{

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

    // ---

    /**
     * Create a new Resource from an array
     *
     * @param $data
     */
    public function __construct($type, $data)
    {
        if(isset($data['responses'])) {
            foreach($data['responses'] as $responseCode => $responseData) {
                $this->responses[$responseCode] = new \Raml\Response(
                        $responseCode,
                        isset($responseData['body']) ? $responseData['body'] : [],
                        isset($responseData['description']) ? $responseData['description'] : null,
                        isset($responseData['headers']) ? $responseData['headers'] : []
                    );
            }
        }

        if(isset($data['queryParameters'])) {
            foreach($data['queryParameters'] as $name => $data) {
                $this->queryParameters[$name] = new \Raml\QueryParameter(
                    isset($data['description']) ? $data['description'] : null,
                    isset($data['type']) ? $data['type'] : 'string'
                );
            }
        }


        $this->body = isset($data['body']) ? $data['body'] : [];
    }

    // ---

    public function getSchemaByType($type)
    {
        return $this->body[$type]['schema'];
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

    public function getQueryParameters()
    {
        return $this->queryParameters;
    }
}