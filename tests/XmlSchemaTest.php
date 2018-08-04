<?php

namespace Raml\Tests;

use PHPUnit\Framework\TestCase;
use Raml\Parser;
use Raml\Schema\Definition\XmlSchemaDefinition;
use Raml\Types\TypeValidationError;
use Raml\ValidatorInterface;

class XmlSchemaTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    /**
     * @return XmlSchemaDefinition
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

    /**
     * @test
     */
    public function shouldReturnXmlSchemeDefinition()
    {
        $this->assertInstanceOf('Raml\Schema\Definition\XmlSchemaDefinition', $this->getSchema());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateCorrectXml()
    {
        $xml = new \DOMDocument();
        $xml->loadXML(
            <<<'XML'
<?xml version="1.0"?>
<api-request>
    <input>v1.0</input>
</api-request>
XML
        );

        $schema = $this->getSchema();
        $schema->validate($xml);
        $this->assertTrue($schema->isValid());
    }

    /**
     * @test
     */
    public function shouldCorrectlyValidateIncorrectXml()
    {
        $xml = new \DOMDocument();
        $xml->loadXML(
            <<<'XML'
<?xml version="1.0"?>
<api-response>
    <input>v1.0</input>
</api-response>
XML
        );

        $schema = $this->getSchema();
        $schema->validate($xml);
        $this->assertValidationFailedWithErrors(
            $schema,
            [
                new TypeValidationError("Element 'api-response': No matching global declaration available for the validation root.\n", 'xml validation'),
            ]
        );
    }

    /**
     * @test
     */
    public function shouldConvertXmlToString()
    {
        $this->assertInternalType('string', (string) $this->loadXmlSchema());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnIncorrectXml()
    {
        $badXml = new \DOMDocument();
        $badXml->loadXML('<api-request></api-request>');

        $schema = $this->loadXmlSchema();
        $schema->validate($badXml);
        $this->assertFalse($schema->isValid());
    }

    /**
     * @return XmlSchemaDefinition
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
     * @param TypeValidationError[] $errors
     */
    private function assertValidationFailedWithErrors(ValidatorInterface $validator, array $errors)
    {
        $this->assertFalse($validator->isValid(), 'Validator expected to fail');
        foreach ($errors as $error) {
            $this->assertContains(
                $error,
                $validator->getErrors(),
                $message = sprintf('Validator expected to contain error: %s', $error->__toString()),
                $ignoreCase = false,
                $checkObjectIdentity = false
            );
        }
    }
}
