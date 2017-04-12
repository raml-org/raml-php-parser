<?php

namespace Raml\Type;

use Raml\Type;
use Raml\TypeCollection;
use Raml\ApiDefinition;
use Raml\Exception\InvalidTypeException;

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
     **/
    private $possibleTypes = [];

    /**
    * Create a new UnionType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return UnionType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        $type->setPossibleTypes(explode('|', $type->getType()));
        $type->setType('union');

        return $type;
    }

    /**
     * Get the value of Possible Types
     *
     * @return array
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
            $this->possibleTypes[] = ApiDefinition::determineType('', ['type' => trim($type)]);
        }

        return $this;
    }

    public function validate($value)
    {
        foreach ($this->getPossibleTypes() as $type) {
            try {
                if ($type->validate($value)) {
                    return true;
                }
            } catch (InvalidTypeException $e) {
                // ignore validation errors since it can be any of possible types
            }
        }
        
        throw new InvalidTypeException(
            [
                'property' => $this->name,
                'constraint' => sprintf(
                    'Value is not any of the following types: %s',
                    array_reduce($this->getPossibleTypes(), function ($carry, $item) {
                        $carry = $carry . ', ' . $item->getName();
                        return $carry;
                    })
                )
            ]
        );
    }
}
