<?php

namespace Raml\FileLoader;

use JsonSchema\Uri\UriRetriever;
use JsonSchema\RefResolver;
use \Raml\Exception\InvalidJsonException;

/**
 * Fetches JSON schema as a string, included refs
 */
class JsonFileLoader implements FileLoaderInterface
{
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
        $retriever = new UriRetriever;
        $jsonSchemaParser = new RefResolver($retriever);
        try {
            return json_encode($jsonSchemaParser->fetchRef('file://' . $filePath, null));
        } catch (\Exception $e) {
            throw new InvalidJsonException(json_last_error());
        }
    }

    /**
     * Get the list of supported extensions
     *
     * @return string[]
     */
    public function getValidExtensions()
    {
        return ['json'];
    }
}
