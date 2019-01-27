<?php

namespace Raml\Schema\Parser;

use Raml\Schema\Definition\XmlSchemaDefinition;
use Raml\Schema\SchemaParserAbstract;

class XmlSchemaParser extends SchemaParserAbstract
{
    /**
     * List of known XML content types
     *
     * @var array
     */
    protected $compatibleContentTypes = [
        'application/xml',
        'text/xml',
        'application/soap+xml',
    ];

    /**
     * Create a new XML Schema definition from a string
     *
     * @param string $schemaString
     *
     * @return XmlSchemaDefinition
     */
    public function createSchemaDefinition($schemaString)
    {
        return new XmlSchemaDefinition($schemaString);
    }
}
