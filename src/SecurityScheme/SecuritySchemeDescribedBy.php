<?php

namespace Raml\SecurityScheme;

use \Raml\ArrayInstantiationInterface;
use \Raml\NamedParameter;
use \Raml\Response;

/**
 * A description of a security scheme
 *
 * @see http://raml.org/spec.html
 */
class SecuritySchemeDescribedBy implements ArrayInstantiationInterface
{

    /**
     * The key of the security scheme
     *
     * @var string
     */
    private $key;

    // --

    /**
     * A list of non default headers (optional)
     *
     * @see http://raml.org/spec.html#headers
     *
     * @var NamedParameter[]
     */
    private $headers = [];

    /**
     * List of query parameters supported by this method
     *
     * @see http://raml.org/spec.html#query-strings
     *
     * @var NamedParameter[]
     */
    private $queryParameters = [];

    /**
     * A  list of possible responses from this method
     *
     * @var Response[]
     */
    private $responses = [];


    // ---

    /**
     * Create a new security scheme description
     *
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Create a new SecuritySchemeDescribedBy from an array of data
     *
     * @param string $key
     * @param array $data
     * [
     *  headers:            ?array
     *  queryParameters:    ?array
     *  responses:          ?array
     * ]
     *
     * @return SecuritySchemeDescribedBy
     */
    public static function createFromArray($key, array $data = [])
    {
        $describedBy = new static($key);

        if (isset($data['headers'])) {
            foreach ($data['headers'] as $key => $header) {
                $describedBy->addHeader(NamedParameter::createFromArray($key, $header));
            }
        }

        if (isset($data['queryParameters'])) {
            foreach ($data['queryParameters'] as $key => $queryParameterData) {
                $describedBy->addQueryParameter(
                    NamedParameter::createFromArray($key, $queryParameterData)
                );
            }
        }

        if (isset($data['responses']) && is_array($data['responses'])) {
            foreach ($data['responses'] as $responseCode => $response) {
                $describedBy->addResponse(
                    Response::createFromArray($responseCode, $response ?: [])
                );
            }
        }

        return $describedBy;
    }

    // --


    /**
     * Returns the headers
     *
     * @return NamedParameter[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Add a new header
     *
     * @param NamedParameter $header
     */
    public function addHeader(NamedParameter $header)
    {
        $this->headers[$header->getKey()] = $header;
    }

    // --

    /**
     * Gets the query parameters of this method
     *
     * @return NamedParameter[]
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }

    /**
     * Add a query parameter
     *
     * @param NamedParameter $queryParameter
     */
    public function addQueryParameter(NamedParameter $queryParameter)
    {
        $this->queryParameters[$queryParameter->getKey()] = $queryParameter;
    }

    // --

    /**
     * Get all the responses
     *
     * @return Response[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Get a response by the response code (200, 404,....)
     *
     * @param integer $responseCode
     *
     * @return Response
     */
    public function getResponse($responseCode)
    {
        return isset($this->responses[$responseCode]) ? $this->responses[$responseCode] : null;
    }

    /**
     * Add a response
     *
     * @param Response $response
     */
    public function addResponse(Response $response)
    {
        $this->responses[$response->getStatusCode()] = $response;
    }
}
