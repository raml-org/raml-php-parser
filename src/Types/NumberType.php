<?php

namespace Raml\Types;

use Raml\Type;

/**
 * NumberType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class NumberType extends Type
{
    /**
     * The minimum value of the parameter. Applicable only to parameters of type number or integer.
     *
     * @var int
     **/
    private $minimum;

    /**
     * The maximum value of the parameter. Applicable only to parameters of type number or integer.
     *
     * @var int
     **/
    private $maximum;

    /**
     * The format of the value. The value MUST be one of the following: int32, int64, int, long, float, double, int16, int8
     *
     * @var string
     **/
    private $format;

    /**
     * A numeric instance is valid against "multipleOf" if the result of dividing the instance by this keyword's value is an integer.
     *
     * @var int
     **/
    private $multipleOf;

    /**
    * Create a new NumberType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return NumberType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'minimum':
                    $type->setMinimum($value);
                    break;
                case 'maximum':
                    $type->setMaximum($value);
                    break;
                case 'format':
                    $type->setFormat($value);
                    break;
                case 'multipleOf':
                    $type->setMultipleOf($value);
                    break;
            }
        }

        return $type;
    }

    /**
     * Get the value of Minimum
     *
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Set the value of Minimum
     *
     * @param int $minimum
     *
     * @return self
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;

        return $this;
    }

    /**
     * Get the value of Maximum
     *
     * @return int
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * Set the value of Maximum
     *
     * @param int $maximum
     *
     * @return self
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * Get the value of Format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the value of Format
     *
     * @param string $format
     *
     * @return self
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the value of Multiple Of
     *
     * @return int
     */
    public function getMultipleOf()
    {
        return $this->multipleOf;
    }

    /**
     * Set the value of Multiple Of
     *
     * @param int $multipleOf
     *
     * @return self
     */
    public function setMultipleOf($multipleOf)
    {
        $this->multipleOf = $multipleOf;

        return $this;
    }
}
