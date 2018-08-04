<?php

namespace Raml\Types;

use Raml\Type;
use Raml\TypeCollection;
use Raml\ApiDefinition;

/**
 * LazyProxyType class for lazy loading datatype objects
 */
class LazyProxyType extends Type
{
    /**
     * @var Type
     */
    private $wrappedObject;

    /**
     * @var Type[]
     */
    private $properties;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->properties = [];
    }

    /**
     * Create a new LazyProxyType from an array of data
     *
     * @param string $name Type name.
     * @param array $data Type data.
     * @return LazyProxyType
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromArray($name, array $data = [])
    {
        $proxy = new static($name);
        $proxy->setDefinition($data);
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Missing "type" key in $data param to determine datatype!');
        }
        if (isset($data['properties'])) {
            $proxy->properties = $data['properties'];
        }

        $proxy->setType($data['type']);
        if ($name !== $data['type']) {
            $proxy->setParent($data['type']);
        }

        return $proxy;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function discriminate($value)
    {
        if (!$this->getWrappedObject()->discriminate($value)) {
            if (isset($value[$this->getDiscriminator()])) {
                $discriminatorValue = $this->getDiscriminatorValue() ?: $this->getName();

                return $value[$this->getDiscriminator()] === $discriminatorValue;
            }

            return true;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getDiscriminator()
    {
        return $this->getResolvedObject()->getDiscriminator();
    }

    /**
     * @return string
     */
    public function getDiscriminatorValue()
    {
        return $this->getResolvedObject()->getDiscriminatorValue();
    }

    /**
     * @return Type[]
     */
    public function getProperties()
    {
        foreach ($this->properties as $name => $property) {
            if (!$property instanceof Type) {
                $property = ApiDefinition::determineType($name, $property);
            }
            $this->properties[$name] = $property;
        }

        return $this->properties;
    }

    /**
     * Magic method to proxy all method calls to original object
     * @param string $name Name of called method.
     * @param array $params Parameters of called method.
     * @return mixed Returns whatever the actual method returns.
     */
    public function __call($name, $params)
    {
        $original = $this->getResolvedObject();

        return call_user_func_array([$original, $name], $params);
    }

    /**
     * @return string
     */
    public function getOriginalType()
    {
        return $this->type;
    }

    public function getRequired()
    {
        if (isset($this->getDefinition()['required'])) {
            return $this->getDefinition()['required'];
        }

        return $this->getResolvedObject()->getRequired();
    }

    /**
     * @param mixed $value
     */
    public function validate($value)
    {
        $this->errors = [];
        $original = $this->getResolvedObject();

        if ($this->discriminate($value)) {
            $original->validate($value);
            if (!$original->isValid()) {
                $this->errors = array_merge($this->errors, $original->getErrors());
            }
        }
    }

    /**
     * @return TypeValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * @return self|Type
     */
    public function getResolvedObject()
    {
        $object = $this->getWrappedObject();
        if (!$object instanceof self) {
            return $object;
        }

        $definition = $object->getDefinitionRecursive();

        return ApiDefinition::determineType($this->getName(), $definition);
    }

    /**
     * @return Type
     */
    public function getWrappedObject()
    {
        if ($this->wrappedObject === null) {
            $typeCollection = TypeCollection::getInstance();
            $this->wrappedObject = $typeCollection->getTypeByName($this->type);
        }

        return $this->wrappedObject;
    }

    /**
     * @return array
     */
    public function getDefinitionRecursive()
    {
        $type = $this->getWrappedObject();
        $typeDefinition = ($type instanceof self) ? $type->getDefinitionRecursive() : $type->getDefinition();
        $recursiveDefinition = array_replace_recursive($typeDefinition, $this->getDefinition());
        $recursiveDefinition['type'] = $typeDefinition['type'];

        return $recursiveDefinition;
    }
}
