<?php

namespace Raml;

use Raml\Types\ObjectType;

/**
 *  Singleton class used to register all types in one place
 **/
class TypeCollection implements \Iterator
{
    /**
     * Hold the class instance.
     *
     * @var self
     **/
    private static $instance = null;

    /**
     * Collection
     *
     * @var array
     **/
    private $collection = [];

    /**
     * Current position
     *
     * @var string
     **/
    private $position = 0;

    /**
     * Types which need to inherit properties from their parent
     *
     * @var ObjectType[]
     **/
    private $typesWithInheritance = [];

    /**
    * prevent initiation from outside, there can be only one!
    *
    **/
    private function __construct()
    {
        $this->collection = [];
        $this->position = 0;
    }
 
    /**
     *  The object is created from within the class itself
     * only if the class has no instance.
     *
     * @return TypeCollection
     **/
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new TypeCollection();
        }
    
        return self::$instance;
    }

    /**
     * {@inheritDoc}
     **/
    public function current()
    {
        return $this->collection[$this->position];
    }

    /**
     * {@inheritDoc}
     **/
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritDoc}
     **/
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritDoc}
     **/
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritDoc}
     **/
    public function valid()
    {
        return isset($this->collection[$this->position]);
    }

    /**
     * Adds a Type to the collection
     *
     * @param \Raml\TypeInterface $type Type to add.
     **/
    public function add(\Raml\TypeInterface $type)
    {
        $this->collection[] = $type;
    }

    /**
     * Remove given Type from the collection
     *
     * @param \Raml\TypeInterface $typeToRemove Type to remove.
     **/
    public function remove(\Raml\TypeInterface $typeToRemove)
    {
        foreach ($this->collection as $key => $type) {
            if ($type === $typeToRemove) {
                unset($this->collection[$key]);
                return;
            }
        }
        throw new \Exception(sprintf('Cannot remove given type %s', var_export($type, true)));
    }

    /**
     * Retrieves a type by name
     *
     * @param string $name Name of the Type to retrieve.
     *
     * @return \Raml\TypeInterface Returns Type matching given name if found.
     * @throws \Exception When no type is found.
     **/
    public function getTypeByName($name)
    {
        foreach ($this->collection as $type) {
            /** @var $type \Raml\TypeInterface */
            if ($type->getName() === $name) {
                return $type;
            }
        }
        throw new \Exception(sprintf('No type found for name %s, list: %s', var_export($name, true), var_export($this->collection, true)));
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasTypeByName($name)
    {
        try {
            return $this->getTypeByName($name) !== null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Applies inheritance on all types that have a parent
     **/
    public function applyInheritance()
    {
        foreach ($this->typesWithInheritance as $key => $type) {
            $type->inheritFromParent();
        }
        // now clear list to prevent applying multiple times on the same objects
        $this->typesWithInheritance = [];
    }

    /**
     * Adds a Type to the list of typesWithInheritance
     *
     * @param ObjectType $type Type to add.
     *
     * @return self Returns self for chaining.
     **/
    public function addTypeWithInheritance(ObjectType $type)
    {
        $this->typesWithInheritance[] = $type;
        return $this;
    }

    /**
     * Returns types in a plain multidimensional array
     *
     * @return array Returns plain array.
     **/
    public function toArray()
    {
        $types = [];
        foreach ($this->collection as $type)
        {
            $types[$type->getName()] = $type->toArray();
        }
        return $types;
    }

    /**
     * Clears the TypeCollection of any registered types
     *
     **/
    public function clear()
    {
        $this->collection = [];
        $this->position = 0;
        $this->typesWithInheritance = [];
    }
}
