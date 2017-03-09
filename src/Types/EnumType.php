<?php

namespace Raml\Types;

use Raml\Type;

class EnumType extends Type
{
    private $enumValues;

    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        if (isset($data['enum'])) {
            $type->setEnumValues($data['enum']);
        }

        return $type;
    }

    public function getEnumValues()
    {
        return $this->enumValues;
    }

    public function setEnumValues($enumValues)
    {
        $this->enumValues = $enumValues;
    }

    public function validate($value)
    {
        parent::validate($value);

        if (!in_array($value, $this->getEnumValues(), true)) {
            $this->errors[] = TypeValidationError::unexpectedValue($this->getName(), $this->getEnumValues(), $value);
        }
    }
}
