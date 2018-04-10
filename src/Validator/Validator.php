<?php

namespace Raml\Validator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raml\NamedParameter;
use Raml\Exception\InvalidSchemaException;
use Raml\Exception\ValidationException;
use Exception;
use Raml\Exception\BodyNotFoundException;
use Raml\Validator\ValidatorSchemaException;
use Raml\Exception\InvalidTypeException;

/**
 * Validator
 */
class Validator
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
     * @throws Exception
     */
    public function validateRequest(RequestInterface $request)
    {
        $this->assertNoMissingParameters($request);
        $this->assertValidParameters($request);
        $this->assertValidRequestBody($request);
    }

    /**
     * @param RequestInterface $request
     * @throws ValidatorRequestException
     */
    private function assertNoMissingParameters(RequestInterface $request)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $schemaParameters = $this->schemaHelper->getQueryParameters($method, $path, true);
        $requestParameters = $this->getRequestParameters($request);

        $missingParameters = array_diff_key($schemaParameters, $requestParameters);
        if (count($missingParameters) === 0) {
            return;
        }

        throw new ValidatorRequestException(sprintf(
            'Missing request parameters required by the schema for `%s %s`: %s',
            strtoupper($method),
            $path,
            join(', ', array_keys($missingParameters))
        ));
    }

    /**
     * @param RequestInterface $request
     * @throws ValidatorRequestException
     */
    private function assertValidParameters(RequestInterface $request)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $schemaParameters = $this->schemaHelper->getQueryParameters($method, $path);
        $requestParameters = $this->getRequestParameters($request);

        /** @var NamedParameter $schemaParameter */
        foreach ($schemaParameters as $schemaParameter) {
            $key = $schemaParameter->getKey();

            if (!array_key_exists($key, $requestParameters)) {
                continue;
            }

            try {
                $schemaParameter->validate($requestParameters[$key]);
            } catch (ValidationException $exception) {
                $message = sprintf(
                    'Request parameter does not match schema for `%s %s`: %s',
                    strtoupper($method),
                    $path,
                    $exception->getMessage()
                );

                throw new ValidatorRequestException($message, 0, $exception);
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @throws ValidatorRequestException
     */
    private function assertValidRequestBody(RequestInterface $request)
    {
        $body = $request->getBody()->getContents();

        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $contentType = $request->getHeaderLine('Content-Type');
        try {
            $schemaBody = $this->schemaHelper->getRequestBody($method, $path, $contentType);
        } catch (BodyNotFoundException $e) {
            // no body is defined for this request, nothing to check!
            return;
        } catch (ValidatorSchemaException $e) {
            // no body is defined for this request, nothing to check!
            return;
        }

        try {
            $schemaBody->getType()->validate($body);
        } catch (InvalidTypeException $exception) {
            $message = sprintf(
                'Request body for %s %s with content type %s does not match schema: %s',
                strtoupper($method),
                $path,
                $contentType,
                $this->getSchemaErrorsAsString($exception->getErrors())
            );

            throw new ValidatorRequestException($message, 0, $exception);
        }
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    private function getRequestParameters(RequestInterface $request)
    {
        parse_str($request->getUri()->getQuery(), $requestParameters);

        return $requestParameters;
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
    
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function validateResponse(RequestInterface $request, ResponseInterface $response)
    {
        $this->assertNoMissingHeaders($request, $response);
        $this->assertValidHeaders($request, $response);
        $this->assertValidResponseBody($request, $response);
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
    private function assertValidResponseBody(RequestInterface $request, ResponseInterface $response)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('Content-Type');

        $schemaBody = $this->schemaHelper->getResponseBody($method, $path, $statusCode, $contentType);

        // psr7 response object should always be rewinded else getContents() only returns the remaining body
        $response->getBody()->rewind();
        $body = $response->getBody()->getContents();

        // parse body to PHP datatypes
        switch ($contentType) {
            case 'application/json':
                $parsedBody = json_decode($body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidJsonException(json_last_error());
                }
                break;
            case 'application/xml':
                // do nothing
                $parsedBody = $body;
                break;
            
            default:
                throw new ValidatorResponseException(sprintf('Unsupported content-type given: %s', $contentType));
        }

        try {
            $schemaBody->getType()->validate($parsedBody);
        } catch (InvalidTypeException $exception) {
            $message = sprintf(
                'Invalid type: %s',
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
}
