<?php

namespace Raml\Tests\NamedParameters;

use PHPUnit\Framework\TestCase;
use Raml\Exception\ValidationException;
use Raml\Parser;

class ParameterTypesTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var object Used in multiple tests
     */
    private $validateBody;

    protected function setUp()
    {
        parent::setUp();
        $this->parser = new Parser();

        $validateRaml = <<<RAML
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
          date-only:
            type: date-only
          datetime-only:
            type: datetime-only
          time-only:
            type: time-only
          datetime:
            type: datetime
          datetime_format:
            type: datetime
            format: Y-m-d H:i:s
RAML;
        $apiDefinition = $this->parser->parseFromString($validateRaml, '');
        $resource = $apiDefinition->getResourceByUri('/');
        $method = $resource->getMethod('post');
        $this->validateBody = $method->getBodyByType('application/x-www-form-urlencoded');
    }

    /**
     * @test
     */
    public function shouldValidateWithoutExceptions()
    {
        // Not Required
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate(null);

        // Valid date
        $namedParameter = $this->validateBody->getParameter('date');
        $namedParameter->validate('Sun, 06 Nov 1994 08:49:37 GMT');

        $this->validateBody->getParameter('date-only')->validate('2018-08-05');
        $this->validateBody->getParameter('datetime-only')->validate('2018-08-05T13:24:55');
        $this->validateBody->getParameter('time-only')->validate('12:30:00');
        $this->validateBody->getParameter('datetime')->validate('2018-08-05T13:24:55+12:00');
        $this->validateBody->getParameter('datetime_format')->validate('2018-08-05 13:24:55');
    }

    /**
     * @test
     */
    public function shouldValidateRequired()
    {
        // Required
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(7);
        $this->expectExceptionMessage('requiredstring is required');
        $namedParameter = $this->validateBody->getParameter('requiredstring');
        $namedParameter->validate(null);
    }

    /**
     * @test
     */
    public function shouldValidateString()
    {
        // When is a string not a string? When it's an integer.
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(3);
        $this->expectExceptionMessage('string is not a string');
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate(1);
    }

    /**
     * @test
     */
    public function shouldValidateShortString()
    {
        // String is too short
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(8);
        $this->expectExceptionMessage('string must be at least 3 characters long');
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate('a');
    }

    /**
     * @test
     */
    public function shouldValidateLongString()
    {
        // String is too long
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(9);
        $this->expectExceptionMessage('string must be no more than 5 characters long');
        $namedParameter = $this->validateBody->getParameter('string');
        $namedParameter->validate('aaaaaa');
    }

    /**
     * @test
     */
    public function shouldValidateNumber()
    {
        // When is a number not a number? When it's an... array!
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(5);
        $this->expectExceptionMessage('number is not a number');
        $namedParameter = $this->validateBody->getParameter('number');
        $namedParameter->validate([]);
    }

    /**
     * @test
     */
    public function shouldValidateSmallNumber()
    {
        // Number is less than the minimum value
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(10);
        $this->expectExceptionMessage('number must be greater than or equal to 1');
        $namedParameter = $this->validateBody->getParameter('number');
        $namedParameter->validate(0);
    }

    /**
     * @test
     */
    public function shouldValidateLargeNumber()
    {
        // Number is more than the maximum value
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(11);
        $this->expectExceptionMessage('number must be less than or equal to 10');
        $namedParameter = $this->validateBody->getParameter('number');
        $namedParameter->validate(11);
    }

    /**
     * @test
     */
    public function shouldValidateInteger()
    {
        // When is aniteger not an integer? Well... you get the picture.
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(4);
        $this->expectExceptionMessage('integer is not an integer');
        $namedParameter = $this->validateBody->getParameter('integer');
        $namedParameter->validate('a');
    }

    /**
     * @test
     */
    public function shouldValidatePattern()
    {
        // Pattern validation
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(12);
        $this->expectExceptionMessage('pattern does not match the specified pattern');
        $namedParameter = $this->validateBody->getParameter('pattern');
        $namedParameter->validate(6);
    }

    /**
     * @test
     */
    public function shouldValidateEnum()
    {
        // Enum validation
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(13);
        $this->expectExceptionMessage('enum must be one of the following: laughing, my, butt, off');
        $namedParameter = $this->validateBody->getParameter('enum');
        $namedParameter->validate('Grandma');
    }

    /**
     * @test
     */
    public function shouldValidateBoolean()
    {
        // Boolean validation
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('boolean is not boolean');
        $namedParameter = $this->validateBody->getParameter('boolean');
        $namedParameter->validate(1);
    }

    /**
     * @test
     */
    public function shouldValidateDate()
    {
        // Date validation
        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(2);
        $this->expectExceptionMessage('date is not a valid date');
        $namedParameter = $this->validateBody->getParameter('date');
        $namedParameter->validate('Sun, 06 Nov 1994 08:49:37 BUNS');
    }

    /** @test */
    public function shouldValidateDateOnly()
    {
        $this->expectException('\Raml\Exception\ValidationException', 'Expected date-only');
        $namedParameter = $this->validateBody->getParameter('date-only');
        $namedParameter->validate('2018-08-05T13:24:55');
    }

    /** @test */
    public function shouldValidateDateTimeOnly()
    {
        $this->expectException('\Raml\Exception\ValidationException', 'Expected datetime-only');
        $namedParameter = $this->validateBody->getParameter('datetime-only');
        $namedParameter->validate('2018-08-05T13:24:55+12:00');
    }

    /** @test */
    public function shouldValidateTimeOnly()
    {
        $this->expectException('\Raml\Exception\ValidationException', 'Expected time-only');
        $namedParameter = $this->validateBody->getParameter('time-only');
        $namedParameter->validate('2018-08-05T13:24:55');
    }

    /** @test */
    public function shouldValidateDateTime()
    {
        $this->expectException('\Raml\Exception\ValidationException', 'Expected datetime');
        $namedParameter = $this->validateBody->getParameter('datetime');
        $namedParameter->validate('2018-08-05 13:24:55');
    }

    /** @test */
    public function shouldValidateDateTimeFormat()
    {
        $this->expectException('\Raml\Exception\ValidationException', 'Expected datetime with format Y-m-d H:i:s');
        $namedParameter = $this->validateBody->getParameter('datetime_format');
        $namedParameter->validate('2018-08-05T13:24:55');
    }

    // ---

    /** @test */
    /**
     * @test
     */
    public function shouldThrowExceptionOnInvalidString()
    {
        $raml = <<<RAML
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default parameter is not a string');
        $this->parser->parseFromString($raml, '');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnInvalidNumber()
    {
        $raml = <<<RAML
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default parameter is not a number');
        $this->parser->parseFromString($raml, '');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnInvalidInteger()
    {
        $raml = <<<RAML
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default parameter is not an integer');
        $this->parser->parseFromString($raml, '');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnInvalidDate()
    {
        $raml = <<<RAML
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default parameter is not a dateTime object');
        $this->parser->parseFromString($raml, '');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnInvalidBoolean()
    {
        $raml = <<<RAML
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default parameter is not a boolean');
        $this->parser->parseFromString($raml, '');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnInvalidFile()
    {
        $raml = <<<RAML
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A default value cannot be set for a file');
        $this->parser->parseFromString($raml, '');
    }
}
