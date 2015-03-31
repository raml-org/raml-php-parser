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

    /**
     * A list of the bodies of this method
     *
     * @var BodyInterface[]
     */
    private $bodyList = [];


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
     * @param string    $key
     * @param array     $data
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

        if (isset($data['body'])) {
            foreach ($data['body'] as $key => $bodyData) {
                if (in_array($key, \Raml\WebFormBody::$validMediaTypes)) {
                    $body = \Raml\WebFormBody::createFromArray($key, $bodyData);
                } else {
                    $body = \Raml\Body::createFromArray($key, $bodyData);
                }

                $describedBy->addBody($body);
            }
        }

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
     * Get the body by type
     *
     * @param string $type
     *
     * @throws \Exception
     *
     * @return BodyInterface
     */
    public function getBodyByType($type)
    {
        if (!isset($this->bodyList[$type])) {
            throw new \Exception('No body of type "' . $type . '"');
        }

        return $this->bodyList[$type];
    }

    /**
     * Get an array of all bodies
     *
     * @return array The array of bodies
     */
    public function getBodies()
    {
        return $this->bodyList;
    }

    /**
     * Add a body
     *
     * @param BodyInterface $body
     */
    public function addBody(\Raml\BodyInterface $body)
    {
        $this->bodyList[$body->getMediaType()] = $body;
    }


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
