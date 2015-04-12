<?php

class XmlSchemaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Raml\Parser
     */
    private $parser;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new \Raml\Parser();
    }

    /**
     * @return \Raml\Schema\Definition\XmlSchemaDefinition
     */
    private function getSchema()
    {
        $raml = <<<'RAML'
#%RAML 0.8

title: World Music API
baseUri: http://example.api.com/{version}
version: v1
/songs:
  get:
    responses:
        200:
          body:
            text/xml:
              schema: |
                <xs:schema attributeFormDefault="unqualified"
                elementFormDefault="qualified"
                xmlns:xs="http://www.w3.org/2001/XMLSchema">
                    <xs:element name="api-request">
                        <xs:complexType>
                            <xs:sequence>
                                <xs:element type="xs:string" name="input"/>
                            </xs:sequence>
                        </xs:complexType>
                    </xs:element>
                </xs:schema>
RAML;

        $simpleRaml = $this->parser->parseFromString($raml, '');
        $resource = $simpleRaml->getResourceByUri('/songs');
        $method = $resource->getMethod('get');
        $response = $method->getResponse(200);
        $body = $response->getBodyByType('text/xml');
        return $body->getSchema();
    }

    // ---

    /** @test */
    public function shouldReturnXmlSchemeDefinition()
    {
        $this->assertInstanceOf('Raml\Schema\Definition\XmlSchemaDefinition', $this->getSchema());
    }

    /** @test */
    public function shouldCorrectlyValidateCorrectXml()
    {
        $xml = <<<'XML'
<?xml version="1.0"?>
<api-request>
    <input>v1.0</input>
</api-request>
XML;

        $this->assertTrue($this->getSchema()->validate($xml));
    }

    /** @test */
    public function shouldCorrectlyValidateIncorrectXml()
    {
        $this->setExpectedException('\Raml\Exception\InvalidSchemaException', 'Invalid Schema.');

        $xml = <<<'XML'
<?xml version="1.0"?>
<api-response>
    <input>v1.0</input>
</api-response>
XML;

        // using a try catch to validate the errors
        try {
            $this->assertTrue($this->getSchema()->validate($xml));
        } catch (\Raml\Exception\InvalidSchemaException $e) {
            $this->assertEquals(1, count($e->getErrors()));
            $this->assertEquals(
                'Element \'api-response\': No matching global declaration available for the validation root.',
                trim($e->getErrors()[0]->message)
            );
            throw $e;
        }
    }

    /** @test */
    public function shouldCorrectlyValidateInvalidXml()
    {
        $this->setExpectedException('\Raml\Exception\InvalidXmlException', 'Invalid Xml.');

        $xml = <<<'XML'
<?xml version="1.0"?>
<api-response>
XML;

        // using a try catch to validate the errors
        try {
            $this->assertTrue($this->getSchema()->validate($xml));
        } catch (\Raml\Exception\InvalidXmlException $e) {
            $this->assertEquals(1, count($e->getErrors()));
            $this->assertEquals(
                'Premature end of data in tag api-response line 2',
                trim($e->getErrors()[0]->message)
            );
            throw $e;
        }
    }

    // ---

    /**
     * Common to all tests
     * @return \Raml\Schema\Definition\XmlSchemaDefinition
     */
    private function loadXmlSchema()
    {
        $xmlRaml = $this->parser->parse(__DIR__ . '/fixture/xmlSchema.raml');
        return $xmlRaml->getResourceByUri('/jobs')
            ->getMethod('get')
            ->getResponse(200)
            ->getBodyByType('text/xml')
            ->getSchema();
    }

    /**
     * Test __toString()
     * @test
     */
    public function shouldConvertXmlToString()
    {
        $this->assertInternalType('string', (string) $this->loadXmlSchema());
    }

    /**
     * Test validate()
     * @test
     */
    public function shouldThrowExceptionOnIncorrectXml()
    {
        $badXml = '<api-request></api-request>';


        $this->setExpectedException('\Raml\Exception\InvalidSchemaException');

        $this->loadXmlSchema()->validate($badXml);
    }

    /**
     * Test validate()
     * @test
     */
    public function shouldThrowExceptionOnInvalidXml()
    {
        $invalidXml = 'api-request></api-request';
        $this->setExpectedException('\Raml\Exception\InvalidXmlException');
        $this->loadXmlSchema()->validate($invalidXml);
    }
}
