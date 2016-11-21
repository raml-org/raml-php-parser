<?php
namespace Raml\Validator;

use Exception;
use Raml\ApiDefinition;
use Raml\Body;
use Raml\Exception\BadParameter\ResourceNotFoundException;
use Raml\MessageSchemaInterface;
use Raml\Method;
use Raml\NamedParameter;

class ValidatorSchemaHelper
{
    /**
     * @var ApiDefinition
     */
    private $apiDefinition;

    /**
     * @param ApiDefinition $api
     */
    public function __construct(ApiDefinition $api)
    {
        $this->apiDefinition = $api;
    }

    /**
     * @param string $method
     * @param string $path
     * @param bool $requiredOnly
     * @return NamedParameter[]
     */
    public function getQueryParameters($method, $path, $requiredOnly = false)
    {
        $method = $this->getMethod($method, $path);

        $out = [];
        /** @var NamedParameter $parameter */
        foreach ($method->getQueryParameters() as $parameter) {
            if ($requiredOnly && !$parameter->isRequired()) {
                continue;
            }

            $out[$parameter->getKey()] = $parameter;
        }

        return $out;
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $contentType
     * @return Body
     */
    public function getRequestBody($method, $path, $contentType)
    {
        $schema = $this->getMethod($method, $path);

        return $this->getBody($schema, $method, $path, $contentType);
    }

    /**
     * @param string $method
     * @param string $path
     * @param int $statusCode
     * @return \Raml\Response
     * @throws ValidatorSchemaException
     */
    public function getResponse($method, $path, $statusCode)
    {
        $responseSchema = $this->getMethod($method, $path)->getResponse($statusCode);

        if (null === $responseSchema) {
            throw new ValidatorSchemaException(sprintf(
                'Schema for %s %s with status code %d was not found in API definition',
                strtoupper($method),
                $path,
                $statusCode
            ));
        }

        return $responseSchema;
    }

    /**
     * @param string $method
     * @param string $path
     * @param int $statusCode
     * @param string $contentType
     * @return Body
     */
    public function getResponseBody($method, $path, $statusCode, $contentType)
    {
        $schema = $this->getResponse($method, $path, $statusCode);

        return $this->getBody($schema, $method, $path, $contentType);
    }

    /**
     * @param string $method
     * @param string $path
     * @param int $statusCode
     * @param bool $requiredOnly
     * @return NamedParameter[]
     */
    public function getResponseHeaders($method, $path, $statusCode, $requiredOnly = false)
    {
        $schema = $this->getResponse($method, $path, $statusCode);

        $out = [];
        /** @var NamedParameter $header */
        foreach ($schema->getHeaders() as $header) {
            if ($requiredOnly && !$header->isRequired()) {
                continue;
            }

            $out[$header->getKey()] = $header;
        }

        return $out;
    }

    /**
     * @param MessageSchemaInterface $schema
     * @param string $method
     * @param string $path
     * @param string $contentType
     * @return Body
     * @throws ValidatorSchemaException
     */
    private function getBody(MessageSchemaInterface $schema, $method, $path, $contentType)
    {
        try {
            $body = $schema->getBodyByType($contentType);
        } catch (Exception $exception) {
            $message = sprintf(
                'Schema for %s %s with content type %s was not found in API definition',
                strtoupper($method),
                $path,
                $contentType
            );
            
            throw new ValidatorSchemaException($message, 0, $exception);
        }

        // BodyInterface does not contain anything of use for validation, so we need to return an actual Body.
        if (!($body instanceof Body)) {
            throw new ValidatorSchemaException(sprintf(
                'Schema for %s %s with content type %s is BodyInterface but not a Body object, so we can\'t use it',
                strtoupper($method),
                $path,
                $contentType
            ));
        }

        return $body;
    }

    /**
     * @param string $path
     * @return \Raml\Resource
     * @throws ValidatorSchemaException
     */
    private function getResource($path)
    {
        try {
            return $this->apiDefinition->getResourceByUri($path);
        } catch (ResourceNotFoundException $exception) {
            $message = sprintf(
                'Schema for URI %s was not found in API definition',
                $path
            );

            throw new ValidatorSchemaException($message, 0, $exception);
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @return Method
     * @throws Exception
     */
    private function getMethod($method, $path)
    {
        $resource = $this->getResource($path);

        try {
            return $resource->getMethod($method);
        } catch (Exception $exception) {
            throw new ValidatorSchemaException(sprintf(
                'Schema for %s %s was not found in API definition',
                strtoupper($method),
                $path
            ));
        }
    }
}
