<?php

namespace Raml\Schema\Definition;

use Raml\Exception\InvalidXmlException;
use Raml\Exception\InvalidSchemaException;
use \Raml\Schema\SchemaDefinitionInterface;

class XmlSchemaDefinition implements SchemaDefinitionInterface
{
    /**
     * The XML schema
     *
     * @var string
     */
    private $xml;

    // --

    /**
     * Create an XML Schema definition
     *
     * @param string $xml
     */
    public function __construct($xml)
    {
        $this->xml = $xml;
    }

    // ---
    // SchemaDefinitionInterface

    /**
     * Validate an XML string against the schema
     *
     * @param $string
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function validate($string)
    {
        $dom = new \DOMDocument;

        $originalErrorLevel = libxml_use_internal_errors(true);

        $dom->loadXML($string);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ($errors) {
            throw new InvalidXmlException($errors);
        }

        // ---

        $dom->schemaValidateSource($this->xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ($errors) {
            throw new InvalidSchemaException($errors);
        }
        
        libxml_use_internal_errors($originalErrorLevel);

        return true;
    }

    /**
     * Returns the XML schema as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->xml;
    }
}
