<?php

namespace Raml\Types;

use LibXMLError;
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
     * @var string
     **/
    private $xml;

    /**
    * Create a new JsonType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return StringType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        /* @var $type StringType */

        $type->xml = $data;
        
        return $type;
    }

    /**
     * Validate an XML string against the schema
     *
     * @param $string
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

        // ---

        $dom->schemaValidateSource($this->xml);
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
