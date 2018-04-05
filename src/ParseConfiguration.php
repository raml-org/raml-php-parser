<?php

namespace Raml;

class ParseConfiguration
{
    /**
     * If directory tree traversal is allowed
     * Enabling this may be a security risk!
     *
     * @var boolean
     */
    private $allowDirectoryTraversal = false;

    /**
     * Should schemas be parsed
     * This is most likely wanted, but does increase time
     *
     * @var boolean
     */
    private $parseSchemas = true;

    /**
     * Should security schemes be merged
     *
     * @var boolean
     */
    private $parseSecuritySchemes = true;

    // ----

    /**
     * Enable directory traversal
     */
    public function enableDirectoryTraversal()
    {
        $this->allowDirectoryTraversal = true;
    }

    /**
     * Disable directory traversal
     */
    public function disableDirectoryTraversal()
    {
        $this->allowDirectoryTraversal = false;
    }

    /**
     * If directory tree traversal is allowed
     *
     * @return boolean
     */
    public function isDirectoryTraversalAllowed()
    {
        return $this->allowDirectoryTraversal;
    }

    // ---

    /**
     * Enable schema parsing
     */
    public function enableSchemaParsing()
    {
        $this->parseSchemas = true;
    }

    /**
     * Disable schema parsing
     */
    public function disableSchemaParsing()
    {
        $this->parseSchemas = false;
    }

    /**
     * Is schema parsing enabled
     *
     * @return boolean
     */
    public function isSchemaParsingEnabled()
    {
        return $this->parseSchemas;
    }

    // ---

    /**
     * Enable security scheme parsing
     */
    public function enableSecuritySchemeParsing()
    {
        $this->parseSecuritySchemes = true;
    }

    /**
     * Disable security scheme parsing
     */
    public function disableSecuritySchemeParsing()
    {
        $this->parseSecuritySchemes = false;
    }

    /**
     * Is security scheme parsing enabled
     *
     * @return boolean
     */
    public function isSchemaSecuritySchemeParsingEnabled()
    {
        return $this->parseSecuritySchemes;
    }
}
