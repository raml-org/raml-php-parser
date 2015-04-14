<?php
namespace Raml\Test\NamedParameters;

class ParameterTypesTest extends \PHPUnit_Framework_TestCase
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

    // ---

    /**
     * Returns a valid Body
     *
     * @throws \Exception
     *
     * @return \Raml\WebFormBody
     */
    private function getValidBody()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    description: A post to do something
    body:
      application/x-www-form-urlencoded:
        formParameters:
          string:
            description: A string key
            type: string
            default: A string
          date:
            description: A date key
            type: date
            default: Sun, 06 Nov 1994 08:49:37 GMT
          integer:
            type: integer
            default: 10
          number:
            type: number
            default: 5.43
          boolean:
            type: boolean
            default: false
          file:
            type: file
RAML;


        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        return $body;
    }

    /** @test */
    public function shouldCorrectlyParseTypeDate()
    {
        $namedParameter = $this->getValidBody()->getParameter('date');

        $this->assertEquals('date', $namedParameter->getType());
        $this->assertInstanceOf('\DateTime', $namedParameter->getDefault());
        $this->assertEquals('1994-11-06 08:49:37', $namedParameter->getDefault()->format('Y-m-d h:i:s'));
        $this->assertEquals(
            '^(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun), (?:[0-2][0-9]|3[01]) (?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4} (?:[01][0-9]|2[0-3]):[012345][0-9]:[012345][0-9] GMT|(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday), (?:[0-2][0-9]|3[01])-(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-\d{2} (?:[01][0-9]|2[0-3]):[012345][0-9]:[012345][0-9] GMT|(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun) (?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (?:[ 1-2][0-9]|3[01]) (?:[01][0-9]|2[0-3]):[012345][0-9]:[012345][0-9] \d{4})$',
            $namedParameter->getValidationPattern()
        );

        $this->assertTrue(
            (bool) preg_match('/'.$namedParameter->getValidationPattern().'/', 'Sun, 06 Nov 1994 08:49:37 GMT')
        );
    }

    /** @test */
    public function shouldCorrectlyParseTypeString()
    {
        $namedParameter = $this->getValidBody()->getParameter('string');

        $this->assertEquals('string', $namedParameter->getType());
        $this->assertEquals('A string', $namedParameter->getDefault());
        $this->assertEquals('^([^/]+)$', $namedParameter->getValidationPattern());

        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'This is a valid string')
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'a/url/likethis')
        );
    }

    /** @test */
    public function shouldCorrectlyParseTypeInteger()
    {
        $namedParameter = $this->getValidBody()->getParameter('integer');

        $this->assertEquals('integer', $namedParameter->getType());
        $this->assertEquals(10, $namedParameter->getDefault());
        $this->assertEquals('^[-+]?[0-9]+$', $namedParameter->getValidationPattern());

        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 1)
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', '1e')
        );
    }

    /** @test */
    public function shouldCorrectlyParseTypeNumber()
    {
        $namedParameter = $this->getValidBody()->getParameter('number');

        $this->assertEquals('number', $namedParameter->getType());
        $this->assertEquals(5.43, $namedParameter->getDefault());
        $this->assertEquals('^[-+]?[0-9]*\.?[0-9]+$', $namedParameter->getValidationPattern());

        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 1)
        );

        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 1.3)
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', '1.1.1')
        );
    }

    /** @test */
    public function shouldCorrectlyParseTypeBoolean()
    {
        $namedParameter = $this->getValidBody()->getParameter('boolean');

        $this->assertEquals('boolean', $namedParameter->getType());
        $this->assertFalse($namedParameter->getDefault());
        $this->assertEquals('^(true|false)$', $namedParameter->getValidationPattern());

        $this->assertTrue(
            (bool) preg_match('/'.$namedParameter->getValidationPattern().'/', 'true')
        );

        $this->assertFalse(
            (bool) preg_match('/'.$namedParameter->getValidationPattern().'/', false)
        );
    }

    /** @test */
    public function shouldCorrectlyParseTypeFile()
    {
        $namedParameter = $this->getValidBody()->getParameter('file');

        $this->assertEquals('file', $namedParameter->getType());
        $this->assertNull($namedParameter->getDefault());
        $this->assertEquals('^([^/]+)$', $namedParameter->getValidationPattern());

        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'filname.raml')
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', '/asd/')
        );
    }

    // ---
    /** @test */
    public function shouldIgnoreTypeCheckValidation()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: string
            default: abcde
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $namedParameter = $body->getParameter('param');
        $this->assertEquals('string', $namedParameter->getType());
        $this->assertEquals('abcde', $namedParameter->getDefault());
        $this->assertEquals(null, $namedParameter->getValidationPattern(false));
    }

    // ---
    /** @test */
    public function shouldParseCustomValidation()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: string
            pattern: ^.{5}$
            default: abcde
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $namedParameter = $body->getParameter('param');
        $this->assertEquals('string', $namedParameter->getType());
        $this->assertEquals('abcde', $namedParameter->getDefault());
        $this->assertEquals('^.{5}$', $namedParameter->getValidationPattern());

        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'abcde')
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'aa')
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'abcdef')
        );
    }

    /** @test */
    public function shouldParseLegacyCustomValidation()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: string
            validationPattern: ^.{5}$
            default: abcde
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $namedParameter = $body->getParameter('param');
        $this->assertEquals('string', $namedParameter->getType());
        $this->assertEquals('abcde', $namedParameter->getDefault());
        $this->assertEquals('^.{5}$', $namedParameter->getValidationPattern());

        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'abcde')
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'aa')
        );

        $this->assertFalse(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'abcdef')
        );
    }

    /** @test */
    public function shouldParseMinAndMax()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: number
            minimum: 3
            maximum: 5
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $namedParameter = $body->getParameter('param');
        $this->assertEquals('number', $namedParameter->getType());
        $this->assertEquals('^[-+]?[0-9]*\.?[0-9]+$', $namedParameter->getValidationPattern());

        $this->assertEquals(3, $namedParameter->getMinimum());
        $this->assertEquals(5, $namedParameter->getMaximum());
    }

    /** @test */
    public function shouldParseMinAndMaxStrings()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: string
            minLength: 3
            maxLength: 5
RAML;

        $apiDefinition = $this->parser->parseFromString($raml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $body = $method->getBodyByType('application/x-www-form-urlencoded');

        $namedParameter = $body->getParameter('param');
        $this->assertEquals('string', $namedParameter->getType());
        $this->assertEquals('^((?!\/).){3,5}$', $namedParameter->getValidationPattern());

        $validations = [
            'aa' => false,
            'aaa' => true,
            'aaaa' => true,
            'aaaaa' => true,
            'aaaaaa' => false,
            'aaa/a' => false
        ];


        $this->assertTrue(
            (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', 'aaaaa')
        );

        foreach($validations as $string => $shouldMatch) {
            $this->assertSame(
                $shouldMatch,
                (bool) preg_match('|'.$namedParameter->getValidationPattern().'|', $string),
                $string
            );
        }
    }

    // ---

    /** @test */
    public function shouldThrowExceptionOnInvalidString()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: string
            default: 10
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a string');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidNumber()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: number
            default: string
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a number');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidInteger()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: integer
            default: 10.1
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not an integer');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidDate()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: date
            default: 2000
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a dateTime object');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidBoolean()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: boolean
            default: 1
RAML;


        $this->setExpectedException('Exception', 'Default parameter is not a boolean');
        $this->parser->parseFromString($raml, '');
    }

    /** @test */
    public function shouldThrowExceptionOnInvalidFile()
    {
        $raml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          param:
            type: file
            default: 1
RAML;


        $this->setExpectedException('Exception', 'A default value cannot be set for a file');
        $this->parser->parseFromString($raml, '');
    }
}
