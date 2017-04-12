<?php

namespace Raml\Type;

use Raml\Type;
use Raml\Exception\InvalidTypeException;

/**
 * StringType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class StringType extends Type
{
    /**
     * Regular expression that this string should match.
     *
     * @var string
     **/
    private $pattern = null;

    /**
     * Minimum length of the string. Value MUST be equal to or greater than 0.
     * Default: 0
     *
     * @var int
     **/
    private $minLength = null;

    /**
     * Maximum length of the string. Value MUST be equal to or greater than 0.
     * Default: 2147483647
     *
     * @var int
     **/
    private $maxLength = null;

    /**
    * Create a new StringType from an array of data
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

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'pattern':
                    $type->setPattern($value);
                    break;
                case 'minLength':
                    $type->setMinLength($value);
                    break;
                case 'maxLength':
                    $type->setMaxLength($value);
                    break;
            }
        }
        
        return $type;
    }

    /**
     * Get the value of Pattern
     *
     * @return mixed
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set the value of Pattern
     *
     * @param mixed $pattern
     *
     * @return self
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get the value of Min Length
     *
     * @return mixed
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set the value of Min Length
     *
     * @param mixed $minLength
     *
     * @return self
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * Get the value of Max Length
     *
     * @return mixed
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set the value of Max Length
     *
     * @param mixed $maxLength
     *
     * @return self
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function validate($value)
    {
        if (!is_string($value)) {
            throw new InvalidTypeException(['property' => $this->name, 'constraint' => 'Value is not a string.']);
        }
        if (!is_null($this->pattern)) {
            if (preg_match('/'.$this->pattern.'/', $value) == false) {
                throw new InvalidTypeException(['property' => $this->name, 'constraint' => sprintf('String does not match required pattern: %s.', $this->pattern)]);
            }
        }
        if (!is_null($this->minLength)) {
            if (strlen($value) < $this->minLength) {
                throw new InvalidTypeException(['property' => $this->name, 'constraint' => sprintf('String is shorter than the minimal length of %s.', $this->minLength)]);
            }
        }
        if (!is_null($this->maxLength)) {
            if (strlen($value) > $this->maxLength) {
                throw new InvalidTypeException(['property' => $this->name, 'constraint' => sprintf('String is longer than the maximal length of %s.', $this->minLength)]);
            }
        }

        return true;
    }
}
