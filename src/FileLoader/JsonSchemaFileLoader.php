<?php

namespace Raml\FileLoader;

use JsonSchema\Uri\UriRetriever;
use JsonSchema\RefResolver;
use Raml\Exception\BadParameter\FileNotFoundException;
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
        $retriever = new UriRetriever;
        $jsonSchemaParser = new RefResolver($retriever);
        try {
            $json = $jsonSchemaParser->fetchRef('file://' . $filePath, null);
        } catch (\Exception $e) {
            $content = file_get_contents($filePath);

            if ($content === false) {
                throw new FileNotFoundException($filePath);
            }

            $json = json_decode($content);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidJsonException(json_last_error());
            }
        }

        $json = json_encode($json);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidJsonException(json_last_error());
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
