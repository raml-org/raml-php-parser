<?php

namespace Raml\Schema;

/**
 * Adapter between third party parser and RAML parser
 *
 */
abstract class SchemaParserAbstract implements SchemaParserInterface
{
    /**
     * The sourceUri of the RAML file for fetching relative paths
     *
     * @var string
     */
    private $sourceUri = null;

    /**
     * List of compatible content types for this parser
     * - Should be populated with any content types that this parser will support
     *
     * @var array
     */
    protected $compatibleContentTypes = [];

    // ---
    // SchemaParserInterface

    /**
     * Set the sourceUri for the RAML file in order to fetch relative paths
     *
     * @param $sourceUri
     */
    public function setSourceUri($sourceUri)
    {
        $this->sourceUri = $sourceUri;
    }

    /**
     * Create a new schema definition from a string
     *
     * @param string $schema
     *
     * @return \Raml\Schema\SchemaDefinitionInterface
     */
    abstract public function createSchemaDefinition($schema);

    /**
     * Returns a list of the compatible content types
     *
     * @return string[]
     */
    public function getCompatibleContentTypes()
    {
        return $this->compatibleContentTypes;
    }

    // --

    /**
     * Get the source uri;
     *
     * @return string
     */
    public function getSourceUri()
    {
        return $this->sourceUri;
    }

    /**
     * Add an aditional supported content type
     *
     * @param $contentType
     */
    public function addCompatibleContentType($contentType)
    {
        if (!in_array($contentType, $this->compatibleContentTypes)) {
            $this->compatibleContentTypes[] = $contentType;
        }
    }
}
