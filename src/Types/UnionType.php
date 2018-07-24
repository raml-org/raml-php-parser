<?php

namespace Raml\Types;

use Raml\Type;
use Raml\ApiDefinition;
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

    public function validate($value)
    {
        $errors = [];

        foreach ($this->getPossibleTypes() as $type) {
            if (!$type->discriminate($value)) {
                continue;
            }

            $type->validate($value);
            if ($type->isValid()) {
                return;
            }

            $errors[$type->getName()] = $type->getErrors();
        }

        $this->errors[] = TypeValidationError::unionTypeValidationFailed($this->getName(), $errors);
    }
}
