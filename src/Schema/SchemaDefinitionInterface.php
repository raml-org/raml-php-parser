<?php

namespace Raml\Schema;

use Raml\ValidatorInterface;

/**
 * Defines the interface for schema definitions.
 * Each schema definition wraps or provides methods for parsing and validating schemas.
 */
interface SchemaDefinitionInterface extends ValidatorInterface
{
    /**
     * Returns the schema as a string
     *
     * @return string
     */
    public function __toString();
}
