<?php

namespace Raml;

/**
 * A response
 *
 * @see http://raml.org/spec.html#responses
 */
class Response implements ArrayInstantiationInterface, MessageSchemaInterface
{
    /**
     * The status code of the response
     *
     * @see http://raml.org/spec.html#responses
     *
     * @var int
     */
    private $statusCode;

    // --

    /**
     * A list of the bodies of this method
     *
     * @see http://raml.org/spec.html#responses
     *
     * @var BodyInterface[]
     */
    private $bodyList;

    /**
     *
     * @see http://raml.org/spec.html#headers
     *
     * @var NamedParameter[]
     */
    private $headers;

    /**
     * The description of the response
     *
     * @see http://raml.org/spec.html#
     *
     * @var string
     */
    private $description;

    // ---

    /**
     * Create a new Response
     *
     * @param int $statusCode
     */
    public function __construct($statusCode)
    {
        $this->statusCode = (int) $statusCode;
        $this->bodyList = [];
        $this->headers = [];
    }

    /**
     * Create a new response object from an array
     *
     * @param string $statusCode
     * @param array $data
     * @return Response
     */
    public static function createFromArray($statusCode, array $data = [])
    {
        $response = new static($statusCode);

        if (isset($data['body']) && is_array($data['body'])) {
            foreach ($data['body'] as $key => $bodyData) {
                $response->addBody(Body::createFromArray($key, $bodyData ?: []));
            }
        }

        if (isset($data['description'])) {
            $response->setDescription($data['description']);
        }

        if (isset($data['headers'])) {
            foreach ($data['headers'] as $key => $header) {
                $response->addHeader(NamedParameter::createFromArray($key, $header));
            }
        }

        return $response;
    }

    // --

    /**
     * Returns the status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    // --

    /**
     * Get the body by type
     *
     * @param string $type
     * @return BodyInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getBodyByType($type)
    {
        if (isset($this->bodyList[$type])) {
            return $this->bodyList[$type];
        }
        if (isset($this->bodyList['*/*'])) {
            return $this->bodyList['*/*'];
        }

        throw new \InvalidArgumentException(sprintf('No body found for type "%s"', $type));
    }

    /**
     * Returns the list of bodies for this response type.
     *
     * @return BodyInterface[]
     */
    public function getBodies()
    {
        return $this->bodyList;
    }

    /**
     * Returns all supported types in response
     *
     * @return string[]
     */
    public function getTypes()
    {
        return array_keys($this->bodyList);
    }

    /**
     * Add a new body
     *
     * @param BodyInterface $body
     */
    public function addBody(BodyInterface $body)
    {
        $this->bodyList[$body->getMediaType()] = $body;
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
     * Returns the description
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
}
