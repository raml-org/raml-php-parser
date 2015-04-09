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
        
        try {
            if ($dom->loadXML($string) === false) {
                throw new InvalidXmlException(2);
            }
            
            if ($dom->schemaValidateSource($this->xml) === false) {
                throw new InvalidSchemaException(array());
            }
             
             
        } catch (\Exception $e) {
            /*
    		 * DOMDocument might issue a warning, and some frameworks might turn warnings into exceptions.
    		 * We'll keep things consistent.
    		 */
            throw new InvalidSchemaException(array($e->getCode(), $e->getMessage()));
             
        }

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

    // ---

    /**
     * Returns the XML Schema as an array
     *
     * Credit: @link http://php.net/manual/en/book.simplexml.php#105330
     *
     * @return array
     */
    public function getXmlArray()
    {
        return json_decode(json_encode(simplexml_load_string($this->xml)), true);
    }
}
