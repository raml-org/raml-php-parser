<?php
namespace Raml\Validator;

use Exception;
use Psr\Http\Message\RequestInterface;
use Raml\Exception\InvalidSchemaException;
use Raml\Exception\ValidationException;
use Raml\NamedParameter;

class RequestValidator
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
        $this->assertValidBody($request);
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
     */
    private function assertValidBody(RequestInterface $request)
    {
        $body = $request->getBody()->getContents();

        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $contentType = $request->getHeaderLine('Content-Type');

        $schemaBody = $this->schemaHelper->getRequestBody($method, $path, $contentType);

        try {
            $schemaBody->getSchema()->validate($body);
        } catch (InvalidSchemaException $exception) {
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
}
