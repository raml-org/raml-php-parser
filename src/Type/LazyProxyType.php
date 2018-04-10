<?php

namespace Raml\Type;

use Raml\TypeInterface;
use Raml\TypeCollection;
use Raml\ApiDefinition;

/**
 * LazyProxyType class for lazy loading datatype objects
 */
class LazyProxyType implements TypeInterface
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

    public function getResolvedObject()
    {
        $object = $this->getWrappedObject();
        if ($object instanceof self) {
            $definition = $object->getDefinitionRecursive();
            return ApiDefinition::determineType($this->name, $definition);
        }
        return $object;
    }

    public function validate($value)
    {
        $original = $this->getResolvedObject();
        return $original->validate($value);
    }
}
