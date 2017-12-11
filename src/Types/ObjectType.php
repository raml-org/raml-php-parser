<?php

namespace Raml\Types;

use Raml\Type;
use Raml\ApiDefinition;

/**
 * ObjectType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class ObjectType extends Type
{
    /**
     * The properties that instances of this type can or must have.
     *
     * @var \Raml\Type[]
     **/
    private $properties = null;

    /**
     * The minimum number of properties allowed for instances of this type.
     *
     * @var int
     **/
    private $minProperties = null;

    /**
     * The maximum number of properties allowed for instances of this type.
     *
     * @var int
     **/
    private $maxProperties = null;

    /**
     * A Boolean that indicates if an object instance has additional properties.
     * Default: true
     *
     * @var bool
     **/
    private $additionalProperties = null;

    /**
     * Determines the concrete type of an individual object at runtime when,
     * for example, payloads contain ambiguous types due to unions or inheritance.
     * The value must match the name of one of the declared properties of a type.
     * Unsupported practices are inline type declarations and using discriminator with non-scalar properties.
     *
     * @var string
     **/
    protected $discriminator = null;

    /**
     * Identifies the declaring type.
     * Requires including a discriminator facet in the type declaration.
     * A valid value is an actual value that might identify the type of an individual object and is unique in the hierarchy of the type.
     * Inline type declarations are not supported.
     * Default: The name of the type
     *
     * @var string
     **/
    private $discriminatorValue = null;

    /**
     * Create a new ObjectType from an array of data
     *
     * @param string                 $name Type name.
     * @param array                  $data Type data.
     *
     * @return ObjectType
     */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        $type->setType('object');

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'properties':
                    $type->setProperties($value);
                    break;
                case 'minProperties':
                    $type->setMinProperties($value);
                    break;
                case 'maxProperties':
                    $type->setMinProperties($value);
                    break;
                case 'additionalProperties':
                    $type->setAdditionalProperties($value);
                    break;
                case 'discriminator':
                    $type->setDiscriminator($value);
                    break;
                case 'discriminatorValue':
                    $type->setDiscriminatorValue($value);
                    break;
            }
        }

        return $type;
    }

    public function discriminate($value)
    {
        if (isset($value[$this->getDiscriminator()])) {
            if ($this->getDiscriminatorValue() !== null) {
                if ($this->getDiscriminatorValue() === $value[$this->getDiscriminator()]) {
                    return true;
                }

                return false;
            }

            return $value[$this->getDiscriminator()] === $this->getName();
        }

        return true;
    }

    /**
     * Get the value of Properties
     *
     * @return Type[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set the value of Properties
     *
     * @param array $properties
     *
     * @return self
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $name => $property) {
            if ($property instanceof \Raml\TypeInterface === false) {
                $property = ApiDefinition::determineType($name, $property);
            }
            $this->properties[] = $property;
        }

        return $this;
    }

    /**
     * Returns a property by name
     *
     * @param string $name
     * @return null|Type
     */
    public function getPropertyByName($name)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        return null;
    }



    /**
     * Get the value of Min Properties
     *
     * @return mixed
     */
    public function getMinProperties()
    {
        return $this->minProperties;
    }

    /**
     * Set the value of Min Properties
     *
     * @param mixed $minProperties
     *
     * @return self
     */
    public function setMinProperties($minProperties)
    {
        $this->minProperties = $minProperties;

        return $this;
    }

    /**
     * Get the value of Max Properties
     *
     * @return mixed
     */
    public function getMaxProperties()
    {
        return $this->maxProperties;
    }

    /**
     * Set the value of Max Properties
     *
     * @param mixed $maxProperties
     *
     * @return self
     */
    public function setMaxProperties($maxProperties)
    {
        $this->maxProperties = $maxProperties;

        return $this;
    }

    /**
     * Get the value of Additional Properties
     *
     * @return mixed
     */
    public function getAdditionalProperties()
    {
        return $this->additionalProperties;
    }

    /**
     * Set the value of Additional Properties
     *
     * @param mixed $additionalProperties
     *
     * @return self
     */
    public function setAdditionalProperties($additionalProperties)
    {
        $this->additionalProperties = $additionalProperties;

        return $this;
    }

    /**
     * Get the value of Discriminator
     *
     * @return mixed
     */
    public function getDiscriminator()
    {
        return $this->discriminator;
    }

    /**
     * Set the value of Discriminator
     *
     * @param mixed $discriminator
     *
     * @return self
     */
    public function setDiscriminator($discriminator)
    {
        $this->discriminator = $discriminator;

        return $this;
    }

    /**
     * Get the value of Discriminator Value
     *
     * @return mixed
     */
    public function getDiscriminatorValue()
    {
        return $this->discriminatorValue;
    }

    /**
     * Set the value of Discriminator Value
     *
     * @param mixed $discriminatorValue
     *
     * @return self
     */
    public function setDiscriminatorValue($discriminatorValue)
    {
        $this->discriminatorValue = $discriminatorValue;

        return $this;
    }

    public function validate($value)
    {
        parent::validate($value);

        // an object is in essence just a group (array) of datatypes
        if (!is_array($value)) {
            if (!is_object($value)) {
                $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'object', $value);
                return;
            }
            // in case of stdClass - convert it to array for convenience
            $value = get_object_vars($value);
        }
        foreach ($this->getProperties() as $property) {
            if ($property->getRequired() && !isset($value[$property->getName()])) {
                $this->errors[] = TypeValidationError::missingRequiredProperty($property->getName());
            }
        }
        foreach ($value as $name => $propertyValue) {
            $property = $this->getPropertyByName($name);
            if (!$property) {
                if ($this->additionalProperties === false) {
                    $this->errors[] = TypeValidationError::unexpectedProperty($name);
                }
                continue;
            }
            if ($propertyValue === null && !$property->getRequired()) {
                continue;
            }
            $property->validate($propertyValue);
            if (!$property->isValid()) {
                $this->errors = array_merge($this->errors, $property->getErrors());
            }
        }
    }
}
