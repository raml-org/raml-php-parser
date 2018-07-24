<?php

namespace Raml;

use Exception;
use Raml\Exception\InvalidKeyException;

/**
 *  Singleton class used to register all traits in one place
 */
class TraitCollection implements \Iterator
{
    /**
     * Hold the class instance.
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Collection
     *
     * @var TraitDefinition[]
     */
    private $collection = [];

    /**
     * Current position
     *
     * @var int
     */
    private $position = 0;

    /**
    * prevent initiation from outside, there can be only one!
    *
    */
    private function __construct()
    {
        $this->collection = [];
        $this->position = 0;
    }

    /**
     * The object is created from within the class itself
     * only if the class has no instance.
     *
     * @return TraitCollection
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return TraitDefinition
     */
    public function current()
    {
        if ($this->valid()) {
            return $this->collection[$this->position];
        }

        throw new InvalidKeyException($this->position);
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->collection[$this->position]);
    }

    /**
     * Adds a Type to the collection
     *
     * @param TraitDefinition $traitToAdd Type to add.
     */
    public function add(TraitDefinition $traitToAdd)
    {
        foreach ($this->collection as $key => $trait) {
            if ($trait === $traitToAdd) {
                throw new Exception(sprintf('Trait already exists %s', var_export($traitToAdd, true)));
            }
        }
        $this->collection[] = $traitToAdd;
    }

    /**
     * Remove given Type from the collection
     *
     * @param TraitDefinition $traitToRemove Type to remove.
     *
     * @throws Exception
     */
    public function remove(TraitDefinition $traitToRemove)
    {
        foreach ($this->collection as $key => $trait) {
            if ($trait === $traitToRemove) {
                unset($this->collection[$key]);

                return;
            }
        }

        throw new Exception(sprintf('Cannot remove given trait %s', var_export($traitToRemove, true)));
    }

    /**
     * Retrieves a trait by name
     *
     * @param string $name Name of the Trait to retrieve.
     *
     * @return TraitDefinition Returns Trait matching given name if found.
     * @throws Exception When no type is found.
     */
    public function getTraitByName($name)
    {
        $variables = [];
        foreach ($this->collection as $trait) {
            if (is_array($name)) {
                $variables = reset($name);
                $name = key($name);
            }
            /** @var $trait TraitDefinition */
            if ($trait->getName() === $name) {
                return $trait->parseVariables($variables);
            }
        }

        throw new Exception(sprintf('No trait found for name %s, list: %s', var_export($name, true), var_export($this->collection, true)));
    }

    /**
     * Returns types in a plain multidimensional array
     *
     * @return array Returns plain array.
     */
    public function toArray()
    {
        $types = [];
        foreach ($this->collection as $trait) {
            $types[$trait->getName()] = $trait->toArray();
        }

        return $types;
    }

    /**
     * Clears the TraitCollection of any registered types
     *
     */
    public function clear()
    {
        $this->collection = [];
        $this->position = 0;
    }
}
