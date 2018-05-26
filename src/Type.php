<?php

namespace Raml;

use Raml\Types;
use Raml\Types\ObjectType;
use Raml\Types\TypeValidationError;

/**
 * Type class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class Type implements ArrayInstantiationInterface, TypeInterface
{
    /**
     * @var Types\TypeValidationError[]
     */
    protected $errors = [];

    /**
     * Parent object
     *
     * @var ObjectType|string
     **/
    private $parent = null;

    /**
     * Key used for type
     *
     * @var string
     **/
    private $name;

    /**
     * Type
     *
     * @var string
     **/
    protected $type;

    /**
     * Required
     *
     * @var bool
     **/
    private $required = true;

    /**
     * Raml definition
     *
     * @var array
     **/
    private $definition;

    /**
     * @var array
     */
    private $enum = [];

    /**
     *  Create new type
     *
     *  @param string   $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Create a new Type from an array of data
     *
     * @param string            $name
     * @param array             $data
     *
     * @return Type
     *
     * @throws \Exception       Thrown when input is incorrect.
     */
    public static function createFromArray($name, array $data = [])
    {
        $class = new static($name);

        $class->setType($data['type']);
        if (isset($data['usage'])) {
            $class->setUsage($data['usage']);
        }
        if (isset($data['required'])) {
            $class->setRequired($data['required']);
        }
        if (isset($data['enum'])) {
            $class->setEnum($data['enum']);
        }
        if (substr($name, -1) === '?') {
            $class->setRequired(false);
            $class->setName(substr($name, 0, -1));
        }
        $class->setDefinition($data);

        return $class;
    }

    public function discriminate($value)
    {
        return true;
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
     * Set the value of name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set the value of type
     *
     * @param string $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return string Returns type property.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set definition
     *
     * @param array $data Definition data of type.
     **/
    public function setDefinition(array $data = [])
    {
        $this->definition = $data;
    }

    /**
     * Get definition
     *
     * @return array Returns definition property.
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Get the value of Required
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set the value of Required
     *
     * @param bool $required
     *
     * @return self
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return array
     */
    public function getEnum()
    {
        return $this->enum;
    }

    /**
     * @param array $enum
     */
    public function setEnum(array $enum)
    {
        $this->enum = $enum;
    }

    /**
     * Get the value of Parent
     *
     * @return ObjectType
     */
    public function getParent()
    {
        if (is_string($this->parent)) {
            $this->parent = TypeCollection::getInstance()->getTypeByName($this->parent);
        }
        return $this->parent;
    }

    /**
     * Returns true when parent property is set
     *
     * @return bool Returns true when parent exists, false if not.
     */
    public function hasParent()
    {
        return ($this->parent !== null);
    }

    /**
     * Set the value of Parent
     *
     * @param ObjectType|string $parent
     *
     * @return self
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Inherit properties from parent (recursively)
     *
     * @return self Returns the new object with inherited properties.
     **/
    public function inheritFromParent()
    {
        if (!$this->hasParent()) {
            return $this;
        }
        $parent = $this->getParent();
        // recurse if 
        if ($parent instanceof $this && $parent->hasParent()) {
            $this->parent = $parent->inheritFromParent();
            unset($parent);
        }
        if ($this->getType() === 'reference') {
            return $this->getParent();
        }
        if (!($this->getParent() instanceof $this)) {
            throw new \Exception(sprintf(
                'Inheritance not possible because of incompatible Types, child is instance of %s and parent is instance of %s',
                get_class($this),
                get_class($this->getParent())
            ));
        }

        // retrieve all getter/setters so we can check all properties for possible inheritance
        $getters = [];
        $setters = [];
        foreach (get_class_methods($this) as $method) {
            $result = preg_split('/^(get|set)(.*)$/', $method, null, PREG_SPLIT_NO_EMPTY);
            if (count($result) === 2) {
                if ($result[0] === 'get') {
                    $getters[lcfirst($result[1])] = $method;
                }
                if ($result[0] === 'set') {
                    $setters[lcfirst($result[1])] = $method;
                }
            }
        }
        $properties = array_keys(array_merge($getters, $setters));
        
        foreach ($properties as $prop) {
            if (!isset($getters[$prop]) || !isset($setters[$prop]))
            {
                continue;
            }
            $getter = $getters[$prop];
            $setter = $setters[$prop];
            $currentValue = $this->$getter();
            // if it is unset, make sure it is equal to parent
            if ($currentValue === null) {
                $this->$setter($this->getParent()->$getter());
            }
            // if it is an array, add parent values
            if (is_array($currentValue)) {
                $newValue = array_merge($this->getParent()->$getter(), $currentValue);
                $this->$setter($newValue);
                continue;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        $this->errors = [];
        if ($this->required && !isset($value)) {
            $this->errors[] = new TypeValidationError($this->getName(), 'required');
        }

        if ($this->getEnum() && !in_array($value, $this->getEnum(), true)) {
            $this->errors[] = TypeValidationError::unexpectedValue($this->getName(), $this->getEnum(), $value);
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
}
