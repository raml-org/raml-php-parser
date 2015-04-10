<?php

class XmlSchemaTest extends PHPUnit_Framework_TestCase
{
    /**
     * The schema string or \Raml\SchemaDefinitionInterface
     * @var mixed
     */
    private $schema;

    /**
     * Common to all tests
     */
    public function setUp()
    {
        parent::setUp();
        $parser = new \Raml\Parser();
        $xmlRaml = $parser->parse(__DIR__ . '/fixture/xmlSchema.raml');
        $this->schema = $xmlRaml->getResourceByUri('/jobs')
            ->getMethod('get')
            ->getResponse(200)
            ->getBodyByType('text/xml')
            ->getSchema();
    }

    /**
     * Test __toString()
     */
    public function testToString()
    {
        $schemaString = (string) $this->schema;
        $this->assertInternalType('string', $schemaString);
    }

    /**
     * Test validate()
     */
    public function testValidate()
    {
        $good_xml       = '<api-request><input>test</input></api-request>';
        $bad_xml        = '<api-request></api-request>';
        $invalid_xml    = 'api-request></api-request';
        
        $this->setExpectedException('\Raml\Exception\InvalidSchemaException');
        $this->schema->validate($bad_xml);
        
        $this->setExpectedException('\Raml\Exception\InvalidXmlException');
        $this->schema->validate($invalid_xml);
    }
}
