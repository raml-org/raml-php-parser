<?php

namespace Raml\Schema;

/**
 * Adapter between third party parser and RAML parser
 *
 */
interface SchemaParserInterface
{
    /**
     * Set the sourceUri for the RAML file in order to fetch relative paths
     *
     * @param $sourceUri
     */
    public function setSourceUri($sourceUri);

    /**
     * Create a new schema definition from a string
     *
     * @param string $schema
     *
     * @return \Raml\Schema\SchemaDefinitionInterface
     */
    public function createSchemaDefinition($schema);

    // --

    /**
     * Returns a list of the compatible content types
     *
     * @return string[]
     */
    public function getCompatibleContentTypes();
}
