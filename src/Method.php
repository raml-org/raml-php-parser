<?php
namespace Raml;

/**
 * Method
 *
 * @see http://raml.org/spec.html#methods
 */
class Method implements ArrayInstantiationInterface
{
    /**
     * Valid METHODS
     * - Currently missing OPTIONS as this is unlikely to be specified in RAML
     * @var array
     */
    public static $validMethods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH'];

    // ---

    /**
     * The method type (required)
     * [GET, POST, PUT, DELETE, PATCH]
     *
     * @see http://raml.org/spec.html#methods
     *
     * @var string
     */
    private $type;

    // --

    /**
     * The description of the method (optional)
     *
     * @see http://raml.org/spec.html#description
     *
     * @var string
     */
    private $description;

    /**
     * Override for the Base Uri Parameters
     *
     * @see http://raml.org/spec.html#base-uri-parameters
     *
     * @var NamedParameter[]
     */
    private $baseUriParameters = [];

    /**
     * A list of non default headers (optional)
     *
     * @see http://raml.org/spec.html#headers
     *
     * @var NamedParameter[]
     */
    private $headers = [];

    /**
     * The supported protocols (default to protocol on baseUrl)
     *
     * @see http://raml.org/spec.html#protocols
     *
     * @var array
     */
    private $protocols = [];

    /**
     * List of query parameters supported by this method
     *
     * @see http://raml.org/spec.html#query-strings
     *
     * @var NamedParameter[]
     */
    private $queryParameters = [];

    /**
     * A list of the bodies of this method
     *
     * @var BodyInterface[]
     */
    private $bodyList = [];

    /**
     * A  list of possible responses from this method
     *
     * @var Response[]
     */
    private $responses = [];

    /**
     * A list of security schemes
     *
     * @var SecurityScheme[]
     */
    private $securitySchemes = [];

    // ---

    /**
     * Create a new Method from an array
     *
     * @param string        $type
     * @param ApiDefinition $apiDefinition
     */
    public function __construct($type, ApiDefinition $apiDefinition)
    {
        $this->type = strtoupper($type);

        foreach ($apiDefinition->getProtocols() as $protocol) {
            $this->addProtocol($protocol);
        }
    }

    /**
     * Create a new Method from an array
     *
     * @param string        $method
     * @param array         $data
     * [
     *  body:               ?array
     *  headers:            ?array
     *  description:        ?string
     *  protocols:          ?array
     *  responses:          ?array
     *  queryParameters:    ?array
     * ]
     * @param ApiDefinition $apiDefinition
     *
     * @throws \Exception
     *
     * @return Method
     */
    public static function createFromArray($method, array $data = [], ApiDefinition $apiDefinition = null)
    {
        $method = new static($method, $apiDefinition);

        if (isset($data['body'])) {
            foreach ($data['body'] as $key => $bodyData) {
                if (in_array($key, WebFormBody::$validMediaTypes)) {
                    $body = WebFormBody::createFromArray($key, $bodyData);
                } else {
                    $body = Body::createFromArray($key, $bodyData);
                }

                $method->addBody($body);
            }
        }

        if (isset($data['headers'])) {
            foreach ($data['headers'] as $key => $header) {
                $method->addHeader(NamedParameter::createFromArray($key, $header));
            }
        }

        if (isset($data['description'])) {
            $method->setDescription($data['description']);
        }

        if (isset($data['baseUriParameters'])) {
            foreach ($data['baseUriParameters'] as $key => $baseUriParameter) {
                $method->addBaseUriParameter(
                    BaseUriParameter::createFromArray($key, $baseUriParameter)
                );
            }
        }

        if (isset($data['protocols'])) {
            foreach ($data['protocols'] as $protocol) {
                $method->addProtocol($protocol);
            }
        }

        if (isset($data['responses']) && is_array($data['responses'])) {
            foreach ($data['responses'] as $responseCode => $response) {
                $method->addResponse(
                    Response::createFromArray($responseCode, $response ?: [])
                );
            }
        }

        if (isset($data['queryParameters'])) {
            foreach ($data['queryParameters'] as $key => $queryParameterData) {
                $method->addQueryParameter(
                    NamedParameter::createFromArray($key, $queryParameterData)
                );
            }
        }

        if (isset($data['securedBy'])) {
            foreach ($data['securedBy'] as $key => $securedBy) {
                if ($securedBy) {
                    if (is_array($securedBy)) {
                        $key = array_keys($securedBy)[0];
                        $securityScheme = clone $apiDefinition->getSecurityScheme($key);
                        $securityScheme->mergeSettings($securedBy[$key]);
                        $method->addSecurityScheme($securityScheme);
                    } else {
                        $method->addSecurityScheme($apiDefinition->getSecurityScheme($securedBy));
                    }
                } else {
                    $method->addSecurityScheme(SecurityScheme::createFromArray('null', array(), $apiDefinition));
                }
            }
        }

        return $method;
    }

    // ---

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    // --

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // --

    /**
     * Get the base uri parameters
     *
     * @return NamedParameter[]
     */
    public function getBaseUriParameters()
    {
        return $this->baseUriParameters;
    }

    /**
     * Add a new base uri parameter
     *
     * @param NamedParameter $namedParameter
     */
    public function addBaseUriParameter(NamedParameter $namedParameter)
    {
        $this->baseUriParameters[$namedParameter->getKey()] = $namedParameter;
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
     * Does the API support HTTP (non SSL) requests?
     *
     * @return boolean
     */
    public function supportsHttp()
    {
        return in_array(ApiDefinition::PROTOCOL_HTTP, $this->protocols);
    }

    /**
     * Does the API support HTTPS (SSL enabled) requests?
     *
     * @return boolean
     */
    public function supportsHttps()
    {
        return in_array(ApiDefinition::PROTOCOL_HTTPS, $this->protocols);
    }

    /**
     * Get the list of support protocols
     *
     * @return array
     */
    public function getProtocols()
    {
        return $this->protocols;
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
     * Add a supported protocol
     *
     * @param string $protocol
     *
     * @throws \InvalidArgumentException
     */
    public function addProtocol($protocol)
    {
        if (!in_array($protocol, [ApiDefinition::PROTOCOL_HTTP, ApiDefinition::PROTOCOL_HTTPS])) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid protocol', $protocol));
        }

        if (in_array($protocol, $this->protocols)) {
            $this->protocols[] = $protocol;
        }
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
    public function addBody(BodyInterface $body)
    {
        $this->bodyList[$body->getMediaType()] = $body;
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

    // --

    /**
     * Get the list of security schemes
     *
     * @return SecurityScheme[]
     */
    public function getSecuritySchemes()
    {
        return $this->securitySchemes;
    }

    /**
     * @param SecurityScheme $securityScheme
     * @param bool $merge Set to true to merge the security scheme data with the method, or false to not merge it.
     */
    public function addSecurityScheme(SecurityScheme $securityScheme, $merge = true)
    {
        $this->securitySchemes[$securityScheme->getKey()] = $securityScheme;

        if ($merge === true) {
            $describedBy = $securityScheme->getDescribedBy();
            if ($describedBy) {
                foreach ($describedBy->getHeaders() as $header) {
                    $this->addHeader($header);
                }
            
                foreach ($describedBy->getResponses() as $response) {
                    $this->addResponse($response);
                }
            
                foreach ($describedBy->getQueryParameters() as $queryParameter) {
                    $this->addQueryParameter($queryParameter);
                }
            
                foreach ($this->getBodies() as $bodyType => $body) {
                    if (in_array($bodyType, array_keys($describedBy->getBodies())) &&
                        in_array($bodyType, WebFormBody::$validMediaTypes)
                    ) {
                        $params = $describedBy->getBodyByType($bodyType)->getParameters();
            
                        foreach ($params as $parameterName => $namedParameter) {
                            $body->addParameter($namedParameter);
                        }
                    }
            
                    $this->addBody($body);
                }
            
            }
            
        }
    }
}
