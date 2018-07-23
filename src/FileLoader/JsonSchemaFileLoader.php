<?php

namespace Raml\FileLoader;

use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Uri\UriRetriever;
use Raml\Exception\InvalidJsonException;

/**
 * Fetches JSON schema as a string, included refs
 */
class JsonSchemaFileLoader implements FileLoaderInterface
{
    /**
     * @var string[]
     */
    private $validExtensions;

    /**
     * @param string[] $validExtensions
     */
    public function __construct($validExtensions = ['json'])
    {
        $this->validExtensions = $validExtensions;
    }

    /**
     * Load a json from a path and resolve references
     *
     * @param string $filePath
     *
     * @throws \Exception
     *
     * @return string
     */
    public function loadFile($filePath)
    {
        $retriever = new UriRetriever();

        try {
            $json = json_encode($retriever->retrieve('file://' . $filePath));
        } catch (JsonDecodingException $exception) {
            throw new InvalidJsonException($exception->getCode());
        }

        return $json;
    }

    /**
     * Get the list of supported extensions
     *
     * @return string[]
     */
    public function getValidExtensions()
    {
        return $this->validExtensions;
    }
}
