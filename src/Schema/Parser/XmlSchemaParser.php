<?php

namespace Raml\Schema\Parser;

use Raml\Schema\SchemaParserAbstract;
use Raml\Schema\Definition\XmlSchemaDefinition;

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
        'application/soap+xml'
    ];

    // ---

    /**
     * Create a new XML Schema definition from a string
     *
     * @param $schemaString
     *
     * @return \Raml\Schema\Definition\XmlSchemaDefinition
     */
    public function createSchemaDefinition($schemaString)
    {
        return new XmlSchemaDefinition($schemaString);
    }
}
