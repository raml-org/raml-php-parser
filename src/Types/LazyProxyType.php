<?php

namespace Raml\Types;

use Raml\ArrayInstantiationInterface;
use Raml\TypeInterface;
use Raml\TypeCollection;
use Raml\ApiDefinition;

/**
 * LazyProxyType class for lazy loading datatype objects
 */
class LazyProxyType implements TypeInterface, ArrayInstantiationInterface
{
    /**
     * name/id of type
     *
     * @var string
     **/
    private $name;

    /**
     * original type name, used for resolving
     *
     * @var string
     **/
    private $type;

    /**
     * original type
     *
     * @var \Raml\TypeInterface
     **/
    private $wrappedObject = null;

    /**
     * raml definition
     *
     * @var array
     **/
    private $definition = [];

    private $errors = [];

    /**
     * Create a new LazyProxyType from an array of data
     *
     * @param string                 $name Type name.
     * @param array                  $data Type data.
     *
     * @return LazyProxyType
     */
    public static function createFromArray($name, array $data = [])
    {
        $proxy = new static();
        $proxy->name = $name;
        $proxy->definition = $data;
        if (!isset($data['type'])) {
            throw new \Exception('Missing "type" key in $data param to determine datatype!');
        }

        $proxy->type = $data['type'];

        return $proxy;
    }

    /**
     * Dumps object to array
     *
     * @return array Object dumped to array.
     */
    public function toArray()
    {
        return $this->definition;
    }

    /**
     * Returns type definition
     *
     * @return array Definition of object.
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Get the value of name
     *
     * @return string Returns name property.
     */
    public function getName()
    {
        return $this->name;
    }

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
     * Magic method to proxy all method calls to original object
     * @param string    $name       Name of called method.
     * @param mixed     $params     Parameteres of called method.
     *
     * @return mixed Returns whatever the actual method returns.
     */
    public function __call($name, $params)
    {
        $original = $this->getResolvedObject();

        return call_user_func_array(array($original, $name), $params);
    }

    public function getRequired()
    {
        if (isset($this->definition['required'])) {
            return $this->definition['required'];
        }

        return $this->getResolvedObject()->getRequired();
    }

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
     * @return boolean
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    public function getResolvedObject()
    {
        $object = $this->getWrappedObject();
        if ($object instanceof self) {
            $definition = $object->getDefinitionRecursive();
            return ApiDefinition::determineType($this->name, $definition);
        }

        return $object;
    }

    public function getWrappedObject()
    {
        if ($this->wrappedObject === null) {
            $typeCollection = TypeCollection::getInstance();
            $this->wrappedObject = $typeCollection->getTypeByName($this->type);
        }

        return $this->wrappedObject;
    }

    public function getDefinitionRecursive()
    {
        $type = $this->getWrappedObject();
        $typeDefinition = ($type instanceof self) ? $type->getDefinitionRecursive() : $type->getDefinition();
        $recursiveDefinition = array_replace_recursive($typeDefinition, $this->getDefinition());
        $recursiveDefinition['type'] = $typeDefinition['type'];

        return $recursiveDefinition;
    }
}
