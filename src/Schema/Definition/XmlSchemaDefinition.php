<?php

namespace Raml\Schema\Definition;

use LibXMLError;
use Raml\Exception\InvalidXmlException;
use Raml\Exception\InvalidSchemaException;
use \Raml\Schema\SchemaDefinitionInterface;
use Raml\Types\TypeValidationError;

class XmlSchemaDefinition implements SchemaDefinitionInterface
{
    /**
     * The XML schema
     *
     * @var string
     */
    private $xml;

    private $errors = [];

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
     */
    public function validate($string)
    {
        $dom = new \DOMDocument;

        $originalErrorLevel = libxml_use_internal_errors(true);

        $dom->loadXML($string);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ($errors) {
            /** @var LibXMLError $error */
            foreach ($errors as $error) {
                $this->errors[] = TypeValidationError::xmlValidationFailed($error->message);
            }

            return;
        }

        $dom->schemaValidateSource($this->xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ($errors) {
            /** @var LibXMLError $error */
            foreach ($errors as $error) {
                $this->errors[] = TypeValidationError::xmlValidationFailed($error->message);
            }

            return;
        }
        
        libxml_use_internal_errors($originalErrorLevel);
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

    /**
     * @return TypeValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return empty($this->errors);
    }
}
