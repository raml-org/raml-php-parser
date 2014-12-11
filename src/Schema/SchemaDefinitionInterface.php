<?php
namespace Raml\Schema;

/**
 * Defines the interface for schema definitions.
 * Each schema definition wraps or provides methods for parsing and validating schemas.
 */
interface SchemaDefinitionInterface
{
    /**
     * Validates a string against the schema
     *
     * @param $string
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function validate($string);

    /**
     * Returns the schema as a string
     *
     * @return string
     */
    public function __toString();
}
