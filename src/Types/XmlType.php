<?php

namespace Raml\Types;

use DOMDocument;
use Raml\Type;

/**
 * XmlType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class XmlType extends Type
{
    /**
     * XML schema
     *
     * @var array
     */
    private $xml;

    /**
    * Create a new JsonType from an array of data
    *
    * @param string $name
    * @param array $data
    * @return self
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        assert($type instanceof self);
        $type->xml = $data;

        return $type;
    }

    /**
     * Validate an XML string against the schema
     *
     * @param mixed $value
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
}
