<?php

namespace Raml\Schema;

/**
 * Adapter between third party parser and RAML parser
 *
 */
abstract class SchemaParserAbstract implements SchemaParserInterface
{
    private $sourceUri = null;

    // ---

    /**
     * List of compatible content types for this parser
     * - Should be populated with any content types that this parser will support
     *
     * @var array
     */
    protected $compatibleContentTypes = [];

    /**
     * Create a new schema definition from a string
     *
     * @param string $schema
     *
     * @return \Raml\Schema\SchemaDefinitionInterface
     */
    abstract public function createSchemaDefinition($schema);

    // --

    public function setSourceUri($sourceUri)
    {
        $this->sourceUri = $sourceUri;
    }

    public function getSourceUri()
    {
        return $this->sourceUri;
    }

    /**
     * Returns a list of the compatible content types
     *
     * @return string[]
     */
    public function getCompatibleContentTypes()
    {
        return $this->compatibleContentTypes;
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
