<?php
namespace Raml\Test\NamedParameters;

class ParameterTypesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Raml\Parser
     */
    private $parser;
    
    /**
     * @var object Used in multiple tests
     */
    private $validateBody;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new \Raml\Parser();
        
        $validateRaml =  <<<RAML
#%RAML 0.8
title: Test named parameters
/:
  post:
    body:
      application/x-www-form-urlencoded:
        formParameters:
          requiredstring:
            type: string
            required: true
          string:
            type: string
            minLength: 3
            maxLength: 5
          number:
            type: number
            minimum: 1
            maximum: 10
          integer:
            type: integer
          pattern:
            type: integer
            pattern: '^[-+]?[0-5]+$'
          enum:
            type: string
            enum: ['laughing', 'my', 'butt', 'off']
          boolean:
            type: boolean
          date:
            type: date
RAML;
        $apiDefinition = $this->parser->parseFromString($validateRaml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $this->validateBody = $method->getBodyByType('application/x-www-form-urlencoded');
    }

    // ---
    
    /** @test */
    public function shouldValidateWithoutExceptions()
    {
        // Not Required
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate(null);
        
        // Valid date
        $namedParameter = $this->validateBody->getParameter('date');
        $namedParameter->validate('Sun, 06 Nov 1994 08:49:37 GMT');
    }
    
    /** @test */
    public function shouldValidateRequired()
    {
        // Required
        $this->setExpectedException('\Raml\Exception\ValidationException', 'requiredstring is required', 7);
        $namedParameter = $this->validateBody->getParameter('requiredstring');
        $namedParameter->validate(null);
    }
    
    /** @test */
    public function shouldValidateString()
    {
        // When is a string not a string? When it's an integer.
        $this->setExpectedException('\Raml\Exception\ValidationException', 'string is not a string', 3);
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate(1);
    }
    
    /** @test */
    public function shouldValidateShortString()
    {
        // String is too short
        $this->setExpectedException('\Raml\Exception\ValidationException', 'string must be at least 3 characters long', 8);
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate('a');
    }
    
    /** @test */
    public function shouldValidateLongString()
    {
        // String is too long
        $this->setExpectedException('\Raml\Exception\ValidationException', 'string must be no more than 5 characters long', 9);
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate('aaaaaa');
    }
    
    /** @test */
    public function shouldValidateNumber()
    {
        // When is a number not a number? When it's an... array!
        $this->setExpectedException('\Raml\Exception\ValidationException', 'number is not a number', 5);
        $namedParameter = $this->validateBody->getParameter('number');
        $namedParameter->validate(array());
    }
    
    /** @test */
    public function shouldValidateSmallNumber()
    {
        // Number is less than the minimum value
        $this->setExpectedException('\Raml\Exception\ValidationException', 'number must be greater than or equal to 1', 10);
        $namedParameter = $this->validateBody->getParameter('number');
        $namedParameter->validate(0);
    }
    
    /** @test */
    public function shouldValidateLargeNumber()
    {
        // Number is more than the maximum value
        $this->setExpectedException('\Raml\Exception\ValidationException', 'number must be less than or equal to 10', 11);
        $namedParameter = $this->validateBody->getParameter('number');
        $namedParameter->validate(11);
    }
    
    /** @test */
    public function shouldValidateInteger()
    {
        // When is aniteger not an integer? Well... you get the picture.
        $this->setExpectedException('\Raml\Exception\ValidationException', 'integer is not an integer', 4);
        $namedParameter = $this->validateBody->getParameter('integer');
        $namedParameter->validate('a');
    }
    
    /** @test */
    public function shouldValidatePattern()
    {
        // Pattern validation
        $this->setExpectedException('\Raml\Exception\ValidationException', 'pattern does not match the specified pattern', 12);
        $namedParameter = $this->validateBody->getParameter('pattern');
        $namedParameter->validate(6);
    }
    
    /** @test */
    public function shouldValidateEnum()
    {
        // Enum validation
        $this->setExpectedException('\Raml\Exception\ValidationException', 'enum must be one of the following: laughing, my, butt, off', 13);
        $namedParameter = $this->validateBody->getParameter('enum');
        $namedParameter->validate('Grandma');
    }
    
    /** @test */
    public function shouldValidateBoolean()
    {
        // Boolean validation
        $this->setExpectedException('\Raml\Exception\ValidationException', 'boolean is not boolean', 1);
        $namedParameter = $this->validateBody->getParameter('boolean');
        $namedParameter->validate(1);
    }
    
    /** @test */
    public function shouldValidateDate()
    {
        // Date validation
        $this->setExpectedException('\Raml\Exception\ValidationException', 'date is not a valid date', 2);
        $namedParameter = $this->validateBody->getParameter('date');
        $namedParameter->validate('Sun, 06 Nov 1994 08:49:37 BUNS');
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
