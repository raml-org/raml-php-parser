<?php

namespace Raml\Validator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raml\Exception\InvalidSchemaException;
use Raml\Exception\ValidationException;
use Raml\NamedParameter;

class ResponseValidator
{
    /**
     * @var ValidatorSchemaHelper
     */
    private $schemaHelper;

    /**
     * @param ValidatorSchemaHelper $schema
     */
    public function __construct(ValidatorSchemaHelper $schema)
    {
        $this->schemaHelper = $schema;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function validateResponse(RequestInterface $request, ResponseInterface $response)
    {
        $this->assertNoMissingHeaders($request, $response);
        $this->assertValidHeaders($request, $response);
        $this->assertValidBody($request, $response);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws ValidatorResponseException
     */
    private function assertNoMissingHeaders(RequestInterface $request, ResponseInterface $response)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $statusCode = $response->getStatusCode();

        $schemaHeaders = $this->schemaHelper->getResponseHeaders($method, $path, $statusCode, true);

        $missingHeaders = array_diff_key($schemaHeaders, $response->getHeaders());
        if (count($missingHeaders) === 0) {
            return;
        }

        throw new ValidatorResponseException(sprintf(
            'Missing response headers required by the schema for %s %s with status code %s: %s',
            strtoupper($method),
            $path,
            $statusCode,
            $this->getNamedParametersAsString($missingHeaders)
        ));
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws ValidatorResponseException
     */
    private function assertValidHeaders(RequestInterface $request, ResponseInterface $response)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $statusCode = $response->getStatusCode();

        $schemaHeaders = $this->schemaHelper->getResponseHeaders($method, $path, $statusCode);

        /** @var NamedParameter $schemaHeader */
        foreach ($schemaHeaders as $schemaHeader) {
            $key = $schemaHeader->getKey();

            /** @var string[] $header */
            foreach ($response->getHeader($key) as $header) {
                foreach ($header as $headerValue) {
                    try {
                        $schemaHeader->validate($headerValue);
                    } catch (ValidationException $exception) {
                        $message = sprintf(
                            'Response header %s with value "%s" for %s %s '.
                                'with status code %s does not match schema: %s',
                            $key,
                            $headerValue,
                            strtoupper($method),
                            $path,
                            $statusCode,
                            $exception->getMessage()
                        );

                        throw new ValidatorResponseException($message, 0, $exception);
                    }
                }
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @throws ValidatorResponseException
     */
    private function assertValidBody(RequestInterface $request, ResponseInterface $response)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('Content-Type');

        $schemaBody = $this->schemaHelper->getResponseBody($method, $path, $statusCode, $contentType);

        $body = $response->getBody()->getContents();

        try {
            $schemaBody->getSchema()->validate($body);
        } catch (InvalidSchemaException $exception) {
            $message = sprintf(
                'Response body for %s %s with content type %s and status code %s does not match schema: %s',
                strtoupper($method),
                $path,
                $contentType,
                $statusCode,
                $this->getSchemaErrorsAsString($exception->getErrors())
            );

            throw new ValidatorResponseException($message, 0, $exception);
        }
    }

    /**
     * @param array $errors
     * @return string
     */
    private function getNamedParametersAsString(array $errors)
    {
        return join(', ', array_map(function (NamedParameter $parameter) {
            return $parameter->getDisplayName();
        }, $errors));
    }

    /**
     * @param array $errors
     * @return string
     */
    private function getSchemaErrorsAsString(array $errors)
    {
        return join(', ', array_map(function (array $error) {
            return sprintf('%s (%s)', $error['property'], $error['constraint']);
        }, $errors));
    }
}
