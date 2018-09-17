<?php

namespace Raml\Schema\Definition;

use DOMDocument;
use Raml\Schema\SchemaDefinitionInterface;
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
     * @param mixed $value
     *
     * @throws \Exception
     */
    public function validate($value)
    {
        if (!$value instanceof DOMDocument) {
            $this->errors[] = TypeValidationError::xmlValidationFailed('Expected value of type DOMDocument');

            return;
        }

        $originalErrorLevel = libxml_use_internal_errors(true);
        $value->schemaValidateSource($this->xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ($errors) {
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
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }
}
