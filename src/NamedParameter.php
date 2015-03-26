<?php
namespace Raml;

use \Raml\Exception\InvalidQueryParameterTypeException;

/**
 * Named Parameters
 *
 * @see http://raml.org/spec.html#named-parameters
 */
class NamedParameter implements ArrayInstantiationInterface
{
    // ---
    // Type constants

    const TYPE_STRING   = 'string';
    const TYPE_NUMBER   = 'number';
    const TYPE_INTEGER  = 'integer';
    const TYPE_DATE     = 'date';
    const TYPE_BOOLEAN  = 'boolean';
    const TYPE_FILE     = 'file';

    /**
     * Valid types
     *
     * @var array
     */
    private $validTypes = [
        self::TYPE_STRING,
        self::TYPE_NUMBER,
        self::TYPE_INTEGER,
        self::TYPE_DATE,
        self::TYPE_BOOLEAN,
        self::TYPE_FILE
    ];

    // ---

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
     * @var integer
     */
    private $minLength;

    /**
     * The maximum length for a string (optional)
     *
     * @see http://raml.org/spec.html#maxlength
     *
     * @var integer
     */
    private $maxLength;

    /**
     * The minimum for a integer or number (optional)
     *
     * @see http://raml.org/spec.html#minimum
     *
     * @var integer
     */
    private $minimum;

    /**
     * The maximum for a integer or number (optional)
     *
     * @see http://raml.org/spec.html#maximum
     *
     * @var integer
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
     * @var boolean
     */
    private $repeat = false;

    /**
     * If the parameter is required (default: false)
     *
     * @see http://raml.org/spec.html#required
     *
     * @var boolean
     */
    private $required = false;

    /**
     * A default value used by the server if not provided
     *
     * @see http://raml.org/spec.html#default
     *
     * @var mixed
     */
    private $default;

    // ---

    /**
     * Create a new Query Parameter
     *
     * @param string  $key
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
     * @param $key
     * @param $data
     * [
     *  displayName:        ?string
     *  description:        ?string
     *  type:               ?["string","number","integer","date","boolean","file"]
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
     * ]
     *
     * @throws \Exception
     *
     * @return NamedParameter
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
            $namedParameter->setDefault($data['default']);
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
        if (!in_array($type, $this->validTypes)) {
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
        if ($this->validationPattern) {
            $pattern = $this->validationPattern;
        } else {
            switch($this->getType()) {
                case self::TYPE_NUMBER:
                    // @see http://www.regular-expressions.info/floatingpoint.html
                    $pattern = '[-+]?[0-9]*\.?[0-9]+';
                    break;
                case self::TYPE_INTEGER:
                    $pattern = '[-+]?[0-9]+';
                    break;
                case self::TYPE_DATE:
                    // @see https://plus.google.com/+NathanOsman/posts/
                    //              5zJAR3SSi8e?pid=5970357977048310818&oid=109692134350783862945

                    $pattern = '([\w!#$%&\'*+.A1--]+)/(EW!#$%&\'*+.*-1--11-)(?:\s*As*([\w!#$%&\'*+.*-1-- ]+)'
                                .'\s*=\s*Mw!#$%&\'*+.A1-1+1"(?:[^V00-\xlf\x7f19\[\x00-\x7f])*"))*';
                    break;
                case self::TYPE_BOOLEAN:
                    $pattern = '(true|false)';
                    break;
                case self::TYPE_FILE:
                    $pattern = '([^/]+)';
                    break;
                case self::TYPE_STRING:
                    if ($this->getMinLength() || $this->getMaxLength()) {
                        $pattern = '([^/]{'.$this->getMinLength().','.$this->getMaxLength().'})';
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
     * @return integer
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set minLength
     *
     * @param integer $minLength
     *
     * @throws \Exception
     */
    public function setMinLength($minLength)
    {
        if ($this->type !== self::TYPE_STRING) {
            throw new \Exception('minLength can only be set on type "string"');
        }

        $this->minLength = (int) $minLength;
    }

    // --

    /**
     * Get the maxLength
     *
     * @return integer
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set maxLength
     *
     * @param integer $minLength
     *
     * @throws \Exception
     */
    public function setMaxLength($maxLength)
    {
        if (!$this->type !== self::TYPE_STRING) {
            throw new \Exception('maxLength can only be set on type "string"');
        }

        $this->maxLength = (int) $maxLength;
    }

    // --

    /**
     * Get the minimum
     *
     * @return integer
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Set minimum
     *
     * @param integer $minimum
     *
     * @throws \Exception
     */
    public function setMinimum($minimum)
    {
        if (!in_array($this->type, [self::TYPE_INTEGER, self::TYPE_NUMBER])) {
            throw new \Exception('minimum can only be set on type "integer" or "number');
        }

        $this->minimum = (int) $minimum;
    }

    // --

    /**
     * Get the maximum
     *
     * @return integer
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * Set maximum
     *
     * @param integer $maximum
     *
     * @throws \Exception
     */
    public function setMaximum($maximum)
    {
        if (!in_array($this->type, [self::TYPE_INTEGER, self::TYPE_NUMBER])) {
            throw new \Exception('maximum can only be set on type "integer" or "number');
        }

        $this->minimum = (int) $maximum;
    }

    // --

    /**
     * Get the example
     *
     * @return string
     */
    public function getExample()
    {
        return $this->examples[0];
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
     * @return boolean
     */
    public function canBeRepeated()
    {
        return $this->repeat;
    }

    /**
     * Set if the parameter can be repeated
     *
     * @param boolean $repeated
     */
    public function setRepeat($repeated)
    {
        $this->repeated = (bool) $repeated;
    }

    // --

    /**
     * Is the parameter required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Set if the parameter is required
     *
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
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
     * Set the default
     *
     * @param mixed $default
     *
     * @throws \Exception
     */
    public function setDefault($default)
    {
        if ($default) {
            switch ($this->type) {
                case self::TYPE_STRING:
                    if (!is_string($default)) {
                        throw new \Exception('Default parameter is not a string');
                    }
                    break;
                case self::TYPE_NUMBER:
                    if (!is_numeric($default)) {
                        throw new \Exception('Default parameter is not a number');
                    }
                    break;
                case self::TYPE_INTEGER:
                    if (!is_integer($default)) {
                        throw new \Exception('Default parameter is not a integer');
                    }
                    break;
                case self::TYPE_DATE:
                    if (!$default instanceof \DateTime) {
                        throw new \Exception('Default parameter is not a dateTime object');
                    }
                    break;
                case self::TYPE_BOOLEAN:
                    if (!is_bool($default)) {
                        throw new \Exception('Default parameter is not a boolean');
                    }
                    break;
                case self::TYPE_FILE:
                    throw new \Exception('A default value cannot be set for a file');
                    break;
                default:
            }
        }

        $this->default = $default;
    }
}
