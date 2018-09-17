<?php

namespace Raml;

use Raml\Exception\InvalidQueryParameterTypeException;
use Raml\Exception\ValidationException;

/**
 * @see http://raml.org/spec.html#named-parameters
 */
class NamedParameter implements ArrayInstantiationInterface
{
    // Type constants

    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_INTEGER = 'integer';
    const TYPE_DATE = 'date';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_FILE = 'file';
    const TYPE_DATE_ONLY = 'date-only';
    const TYPE_TIME_ONLY = 'time-only';
    const TYPE_DATETIME_ONLY = 'datetime-only';
    const TYPE_DATETIME = 'datetime';
    const TYPE_ARRAY = 'array';

    // ---
    // Validation exception codes
    const VAL_NOTBOOLEAN = 1;
    const VAL_NOTDATE = 2;
    const VAL_NOTSTRING = 3;
    const VAL_NOTINT = 4;
    const VAL_NOTNUMBER = 5;
    const VAL_NOTFILE = 6; // Unused
    const VAL_ISREQUIRED = 7;
    const VAL_TOOSHORT = 8;
    const VAL_TOOLONG = 9;
    const VAL_NUMLESSTHAN = 10;
    const VAL_GREATERTHAN = 11;
    const VAL_PATTERNFAIL = 12;
    const VAL_NOTENUMVALUE = 13;

    /**
     * Valid types
     *
     * @var string[]
     */
    protected $validTypes = [
        self::TYPE_STRING,
        self::TYPE_NUMBER,
        self::TYPE_INTEGER,
        self::TYPE_DATE,
        self::TYPE_BOOLEAN,
        self::TYPE_FILE,
        self::TYPE_DATETIME_ONLY,
        self::TYPE_DATE_ONLY,
        self::TYPE_TIME_ONLY,
        self::TYPE_DATETIME,
        self::TYPE_ARRAY,
    ];

    /**
     * The key of the named parameter (required)
     *
     * @var string
     */
    private $key;

    /**
     * The display name (optional)
     *
     * @see http://raml.org/spec.html#displayname
     *
     * @var string
     */
    private $displayName;

    /**
     * The description of the parameter (optional)
     *
     * @see http://raml.org/spec.html#description
     *
     * @var string
     */
    private $description;

    /**
     * The primitive type of the parameter (default: string)
     *
     * @see http://raml.org/spec.html#type
     *
     * @var string
     */
    private $type = self::TYPE_STRING;


    /**
     * List of valid values for the parameter (optional)
     *
     * @see http://raml.org/spec.html#enum
     *
     * @var array
     */
    private $enum;

    /**
     * A regular expression pattern for the string to match against (optional)
     *
     * @see http://raml.org/spec.html#pattern
     *
     * @var string
     */
    private $validationPattern;

    /**
     * The minimum length for a string (optional)
     *
     * @see http://raml.org/spec.html#minlength
     *
     * @var int
     */
    private $minLength;

    /**
     * The maximum length for a string (optional)
     *
     * @see http://raml.org/spec.html#maxlength
     *
     * @var int
     */
    private $maxLength;

    /**
     * The minimum for a integer or number (optional)
     *
     * @see http://raml.org/spec.html#minimum
     *
     * @var int
     */
    private $minimum;

    /**
     * The maximum for a integer or number (optional)
     *
     * @see http://raml.org/spec.html#maximum
     *
     * @var int
     */
    private $maximum;

    /**
     * An list of examples (optional)
     *
     * @see http://raml.org/spec.html#example
     *
     * @var array
     */
    private $examples;


    /**
     * Whether the parameter can be used multiple times (default: false)
     *
     * @see http://raml.org/spec.html#repeat
     *
     * @var bool
     */
    private $repeat = false;

    /**
     * If the parameter is required (default: false)
     *
     * @see http://raml.org/spec.html#required
     *
     * @var bool
     */
    protected $required = false;

    /**
     * A default value used by the server if not provided
     *
     * @see http://raml.org/spec.html#default
     *
     * @var mixed
     */
    private $default;

    /**
     * DateTime format (for datetime type only)
     *
     * @var string
     */
    private $format;

    // ---

    /**
     * Create a new Query Parameter
     *
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Get the query parameter key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    // ---

    /**
     * Create a Query Parameter from an Array
     *
     * @param string $key
     * @param array $data
     * [
     *  displayName:        ?string
     *  description:        ?string
     *  type:               ?["string","number","integer","date","boolean","file", ...]
     *  enum:               ?array
     *  pattern:            ?string
     *  validationPattern:  ?string
     *  minLength:          ?integer
     *  maxLength:          ?integer
     *  minimum:            ?integer
     *  maximum:            ?integer
     *  example:            ?string
     *  examples:           ?array
     *  repeat:             ?boolean
     *  required:           ?boolean
     *  default:            ?string
     *  format:             ?string
     * ]
     *
     * @throws \Exception
     *
     * @return self
     */
    public static function createFromArray($key, array $data = [])
    {
        $namedParameter = new static($key);

        if (isset($data['displayName'])) {
            $namedParameter->setDisplayName($data['displayName']);
        }

        if (isset($data['description'])) {
            $namedParameter->setDescription($data['description']);
        }

        if (isset($data['type'])) {
            $namedParameter->setType($data['type']);
        }

        if (isset($data['enum'])) {
            $namedParameter->setEnum($data['enum']);
        }

        if (isset($data['pattern'])) {
            $namedParameter->setValidationPattern($data['pattern']);
        }

        // RAML 1.0
        if (isset($data['validationPattern'])) {
            $namedParameter->setValidationPattern($data['validationPattern']);
        }

        if (isset($data['minLength'])) {
            $namedParameter->setMinLength($data['minLength']);
        }

        if (isset($data['maxLength'])) {
            $namedParameter->setMaxLength($data['maxLength']);
        }

        if (isset($data['minimum'])) {
            $namedParameter->setMinimum($data['minimum']);
        }

        if (isset($data['maximum'])) {
            $namedParameter->setMaximum($data['maximum']);
        }

        if (isset($data['example'])) {
            $namedParameter->addExample($data['example']);
        }

        if (isset($data['examples'])) {
            foreach ($data['examples'] as $example) {
                $namedParameter->addExample($example);
            }
        }

        if (isset($data['repeat'])) {
            $namedParameter->setRepeat($data['repeat']);
        }

        if (isset($data['required'])) {
            $namedParameter->setRequired($data['required']);
        }

        if (isset($data['default'])) {
            if ($namedParameter->getType() === self::TYPE_DATE) {
                $namedParameter->setDefault(\DateTime::createFromFormat('D, d M Y H:i:s T', $data['default']));
            } else {
                $namedParameter->setDefault($data['default']);
            }
        }

        if (isset($data['format'])) {
            $namedParameter->setFormat($data['format']);
        }

        return $namedParameter;
    }

    // ---

    /**
     * Get the display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return ($this->displayName) ?: $this->key;
    }

    /**
     * Set the display name
     *
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    // --

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // --

    /**
     * Get the type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type
     *
     * @param string $type
     *
     * @throws \Exception
     */
    public function setType($type = 'string')
    {
        if (!in_array($type, $this->validTypes, true)) {
            throw new InvalidQueryParameterTypeException($type, $this->validTypes);
        }

        $this->type = $type;
    }

    // --

    /**
     * Get the allowed values
     *
     * @return array
     */
    public function getEnum()
    {
        return $this->enum;
    }

    /**
     * Set the allowed values
     *
     * @param array $enum
     */
    public function setEnum(array $enum)
    {
        $this->enum = $enum;
    }

    // --

    /**
     * Get the pattern regular expression
     *
     * @return string
     */
    public function getValidationPattern()
    {
        return $this->validationPattern;
    }

    /**
     * Set the pattern regular expression
     *
     * @param string $validationPattern
     */
    public function setValidationPattern($validationPattern)
    {
        $this->validationPattern = $validationPattern;
    }

    // --

    /**
     * Get the minLength
     *
     * @return int
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set minLength
     *
     * @param int $minLength
     *
     * @throws \Exception
     */
    public function setMinLength($minLength)
    {
        if ($this->type !== self::TYPE_STRING) {
            throw new \Exception('minLength can only be set on type "string"');
        }

        $this->minLength = (int)$minLength;
    }

    // --

    /**
     * Get the maxLength
     *
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set maxLength
     *
     * @param int $maxLength
     *
     * @throws \Exception
     */
    public function setMaxLength($maxLength)
    {
        if ($this->type !== self::TYPE_STRING) {
            throw new \Exception('maxLength can only be set on type "string"');
        }

        $this->maxLength = (int)$maxLength;
    }

    // --

    /**
     * Get the minimum
     *
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Set minimum
     *
     * @param int $minimum
     *
     * @throws \Exception
     */
    public function setMinimum($minimum)
    {
        if (!in_array($this->type, [self::TYPE_INTEGER, self::TYPE_NUMBER], true)) {
            throw new \Exception('minimum can only be set on type "integer" or "number');
        }

        $this->minimum = (int)$minimum;
    }

    // --

    /**
     * Get the maximum
     *
     * @return int
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * Set maximum
     *
     * @param int $maximum
     *
     * @throws \Exception
     */
    public function setMaximum($maximum)
    {
        if (!in_array($this->type, [self::TYPE_INTEGER, self::TYPE_NUMBER], true)) {
            throw new \Exception('maximum can only be set on type "integer" or "number');
        }

        $this->maximum = (int)$maximum;
    }

    // --

    /**
     * Get the example
     *
     * @param int $position
     *
     * @return string
     */
    public function getExample($position = 0)
    {
        return $this->examples[$position];
    }

    /**
     * Get all examples
     *
     * @return string[]
     */
    public function getExamples()
    {
        return $this->examples;
    }

    /**
     * Set the example
     *
     * @param string $example
     */
    public function addExample($example)
    {
        $this->examples[] = $example;
    }

    // --

    /**
     * Can the parameter be repeated
     *
     * @return bool
     */
    public function canRepeat()
    {
        return $this->repeat;
    }

    /**
     * Set if the parameter can be repeated
     *
     * @param bool $repeat
     */
    public function setRepeat($repeat)
    {
        $this->repeat = (bool)$repeat;
    }

    // --

    /**
     * Is the parameter required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Set if the parameter is required
     *
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = (bool)$required;
    }

    // --

    /**
     * Return the default
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Set the default
     *
     * @param mixed $default
     *
     * @throws \InvalidArgumentException
     */
    public function setDefault($default)
    {
        switch ($this->type) {
            case self::TYPE_STRING:
                if (!is_string($default)) {
                    throw new \InvalidArgumentException('Default parameter is not a string');
                }

                break;
            case self::TYPE_NUMBER:
                if (!is_numeric($default)) {
                    throw new \InvalidArgumentException('Default parameter is not a number');
                }

                break;
            case self::TYPE_INTEGER:
                if (!is_numeric($default) || (int)$default != $default) {
                    throw new \InvalidArgumentException('Default parameter is not an integer');
                }

                break;
            case self::TYPE_DATE:
                if (!$default instanceof \DateTime) {
                    throw new \InvalidArgumentException('Default parameter is not a dateTime object');
                }

                break;
            case self::TYPE_BOOLEAN:
                if (!is_bool($default)) {
                    throw new \InvalidArgumentException('Default parameter is not a boolean');
                }

                break;
            case self::TYPE_FILE:
                throw new \InvalidArgumentException('A default value cannot be set for a file');

                break;
        }

        $this->default = $default;
    }

    /**
     * Validate a parameter via RAML specifications
     *
     * @param mixed $param The value of the parameter to validate
     * @throws ValidationException The code corresponds to the error that occurred.
     */
    public function validate($param)
    {
        /*
         * If we don't have a value to validate, check if it's required.
         * @link http://raml.org/spec.html#required
         */
        if (in_array($param, [null, ''], true)) {
            if ($this->isRequired()) {
                throw new ValidationException($this->getKey().' is required', static::VAL_ISREQUIRED);
            }

            return;
        }

        switch ($this->getType()) {
            case static::TYPE_BOOLEAN:

                // Must be boolean
                if (!is_bool($param)) {
                    throw new ValidationException($this->getKey().' is not boolean', static::VAL_NOTBOOLEAN);
                }

                break;

            case static::TYPE_DATE:

                // Must be a valid date
                if (\DateTime::createFromFormat('D, d M Y H:i:s T', $param) === false) {
                    throw new ValidationException($this->getKey().' is not a valid date', static::VAL_NOTDATE);
                }

            // no break
            case static::TYPE_STRING:

                // Must be a string
                if (!is_string($param)) {
                    throw new ValidationException($this->getKey().' is not a string', static::VAL_NOTSTRING);
                }

                /**
                 * Check the length of a string.
                 *
                 * @link http://raml.org/spec.html#minlength
                 */
                $minLength = $this->getMinLength();
                if (!empty($minLength) && strlen($param) < $minLength) {
                    throw new ValidationException(
                        $this->getKey().' must be at least '.$minLength.' characters long',
                        static::VAL_TOOSHORT
                    );
                }

                /**
                 * Check the length of a string.
                 *
                 * @link http://raml.org/spec.html#maxlength
                 */
                $maxLength = $this->getMaxLength();
                if (!empty($maxLength) && strlen($param) > $maxLength) {
                    throw new ValidationException(
                        $this->getKey().' must be no more than '.$maxLength.' characters long',
                        static::VAL_TOOLONG
                    );
                }

                break;

            case static::TYPE_INTEGER:

                /*
                 * Integers must be of type integer.
                 *
                 * @link http://raml.org/spec.html#type
                 */
                if (!is_int($param)) {
                    throw new ValidationException($this->getKey().' is not an integer', static::VAL_NOTINT);
                }
            // no break

            case static::TYPE_NUMBER:

                /*
                 * Number types must be numeric. ;)
                 *
                 * @link http://raml.org/spec.html#type
                 */
                if (!is_numeric($param)) {
                    throw new ValidationException($this->getKey().' is not a number', static::VAL_NOTNUMBER);
                }

                /**
                 * Check the value constraints if specified.
                 *
                 * @link http://raml.org/spec.html#minimum
                 */
                $min = $this->getMinimum();
                if (!empty($min) && $param < $min) {
                    throw new ValidationException(
                        $this->getKey().' must be greater than or equal to '.$min,
                        static::VAL_NUMLESSTHAN
                    );
                }

                /**
                 * Check the value constraints if specified.
                 *
                 * @link http://raml.org/spec.html#maximum
                 */
                $max = $this->getMaximum();
                if (!empty($max) && $param > $max) {
                    throw new ValidationException(
                        $this->getKey().' must be less than or equal to '.$max,
                        static::VAL_GREATERTHAN
                    );
                }

                break;

            case static::TYPE_FILE:
                // File type cannot be reliably validated based on its type alone.

                break;

            case static::TYPE_TIME_ONLY:
            case static::TYPE_DATE_ONLY:
            case static::TYPE_DATETIME_ONLY:
            case static::TYPE_DATETIME:
                $typeObject = ApiDefinition::determineType(
                    $this->key,
                    [
                        'type' => $this->getType(),
                        'format' => $this->getFormat(),
                    ]
                );
                $typeObject->validate($param);
                if (!$typeObject->isValid()) {
                    throw new ValidationException($typeObject->getErrors()[0]);
                }
                break;
        }

        /**
         * Validate against the RAML specified pattern if it exists.
         *
         * @link http://raml.org/spec.html#pattern
         */
        $validationPattern = $this->getValidationPattern();
        if (!empty($validationPattern) &&
            preg_match('|'.$validationPattern.'|', $param) !== 1
        ) {
            throw new ValidationException(
                $this->getKey().' does not match the specified pattern',
                static::VAL_PATTERNFAIL
            );
        }

        /*
         * If we have an enum (array), then it must be a specified value.
         *
         * NOTE: The RAML spec states that "enum" only applies to strings. However, it
         * could just as easily apply to integers or any other data type that an array
         * can hold in a YAML structure.
         *
         * @link http://raml.org/spec.html#enum
         */
        if (is_array($enum = $this->getEnum()) && !in_array($param, $enum, true)) {
            throw new ValidationException(
                $this->getKey().' must be one of the following: '.implode(', ', $enum),
                static::VAL_NOTENUMVALUE
            );
        }
    }

    /**
     * Get a regex pattern for matching the parameter
     *
     * @return string
     */
    public function getMatchPattern()
    {
        if ($this->validationPattern) {
            $pattern = $this->validationPattern;
        } elseif ($enum = $this->getEnum()) {
            $pattern = '^('.implode('|', array_map('preg_quote', $enum)).')$';
        } else {
            switch ($this->getType()) {
                case self::TYPE_NUMBER:
                    // @see http://www.regular-expressions.info/floatingpoint.html
                    $pattern = '[-+]?[0-9]*\.?[0-9]+';

                    break;
                case self::TYPE_INTEGER:
                    $pattern = '[-+]?[0-9]+';

                    break;
                case self::TYPE_DATE:
                    // @see https://snipt.net/DamienGarrido/
                    //          http-date-regular-expression-validation-rfc-1123rfc-850asctime-f64e6aa3/
                    $pattern = '^(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun), (?:[0-2][0-9]|3[01]) '.
                        '(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4} '.
                        '(?:[01][0-9]|2[0-3]):[012345][0-9]:[012345][0-9] '.
                        'GMT|(?:Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday), '.
                        '(?:[0-2][0-9]|3[01])-(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-\d{2} '.
                        '(?:[01][0-9]|2[0-3]):[012345][0-9]:[012345][0-9] GMT|(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun) '.
                        '(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (?:[ 1-2][0-9]|3[01]) '.
                        '(?:[01][0-9]|2[0-3]):[012345][0-9]:[012345][0-9] \d{4})$';

                    break;
                case self::TYPE_BOOLEAN:
                    $pattern = '(true|false)';

                    break;
                case self::TYPE_FILE:
                    $pattern = '([^/]+)';

                    break;
                case self::TYPE_STRING:
                    if ($this->getMinLength() || $this->getMaxLength()) {
                        $pattern = '((?!\/).){'.$this->getMinLength().','.$this->getMaxLength().'}';
                    } else {
                        $pattern = '([^/]+)';
                    }

                    break;
                default:
                    $pattern = '([^/]+)';
            }
        }

        return $pattern;
    }
}
