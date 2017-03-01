<?php

namespace Raml\Types;

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

        $this->xml = $data;
        
        return $type;
    }

    /**
     * Validate an XML string against the schema
     *
     * @param $string
     *
     * @return bool
     */
    public function validate($string)
    {
        $dom = new \DOMDocument;

        $originalErrorLevel = libxml_use_internal_errors(true);

        $dom->loadXML($string);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ($errors) {
            return false;
        }

        // ---

        $dom->schemaValidateSource($this->xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if ($errors) {
            return false;
        }
        
        libxml_use_internal_errors($originalErrorLevel);

        return true;
    }
}
