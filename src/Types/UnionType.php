<?php

namespace Raml\Types;

use Raml\ApiDefinition;
use Raml\Type;
use Raml\TypeInterface;

/**
 * UnionType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class UnionType extends Type
{
    /**
     * Possible Types
     *
     * @var array
     */
    private $possibleTypes = [];

    /**
     * @var Type[]
     */
    private $properties = [];

    /**
     * @var Type[]
     */
    private $additionalProperties;

    /**
     * Create a new UnionType from an array of data
     *
     * @param string $name
     * @param array $data
     *
     * @return UnionType
     */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        assert($type instanceof self);
        $type->setPossibleTypes(explode('|', $type->getType()));
        $type->setType('union');

        if (isset($data['properties'])) {
            $type->setProperties($data['properties']);
        }

        if (isset($data['additionalProperties'])) {
            $type->setAdditionalProperties($data['additionalProperties']);
        }

        return $type;
    }

    /**
     * Get the value of Possible Types
     *
     * @return TypeInterface[]
     */
    public function getPossibleTypes()
    {
        return $this->possibleTypes;
    }

    /**
     * Set the value of Possible Types
     *
     * @param array $possibleTypes
     *
     * @return self
     */
    public function setPossibleTypes(array $possibleTypes)
    {
        foreach ($possibleTypes as $type) {
            $this->possibleTypes[] = ApiDefinition::determineType(trim($type), ['type' => trim($type)]);
        }

        return $this;
    }

    /**
     * Set the value of Properties
     *
     * @param array $properties
     * @return self
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $name => $property) {
            if (!$property instanceof Type) {
                $property = ApiDefinition::determineType($name, $property);
            }
            $this->properties[] = $property;
        }

        return $this;
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

    public function validate($value)
    {
        $selfProperties = [];
        $selfValue = [];
        $unionValue = $value;
        foreach ($this->getProperties() as $property) {
            $propName = $property->getName();
            $selfProperties[] = $propName;
            if (isset($value[$propName])) {
                $selfValue[$propName] = $value[$propName];
                unset($unionValue[$propName]);
            }
        }

        $typeErrors = [];
        foreach ($this->getPossibleTypes() as $type) {
            if (!$type->discriminate($value)) {
                continue;
            }

            $errors = [];

            foreach ($this->getProperties() as $property) {
                if ($property->getRequired() && !array_key_exists($property->getName(), $selfValue)) {
                    $errors[] = TypeValidationError::missingRequiredProperty($property->getName());
                }
            }

            if (is_array($selfValue)) {
                foreach ($selfValue as $name => $propertyValue) {
                    $property = $this->getPropertyByName($name);
                    if ($property === null) {
                        if (!$this->additionalProperties) {
                            $errors[] = TypeValidationError::unexpectedProperty($name);
                        }

                        continue;
                    }

                    $property->validate($propertyValue);
                    if (!$property->isValid()) {
                        $errors = array_merge($errors, $property->getErrors());
                    }
                }
            }

            $type->validate($unionValue);
            if (!$type->isValid()) {
                $errors = array_merge($errors, $type->getErrors());
            }

            if (!$errors) {
                return;
            }

            $typeErrors[$type->getName()] = $errors;
        }

        $this->errors[] = TypeValidationError::unionTypeValidationFailed($this->getName(), $typeErrors);
    }
}
