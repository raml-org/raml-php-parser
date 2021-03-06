<?php

namespace Raml\Types;

use Raml\ApiDefinition;
use Raml\Type;
use Raml\TypeCollection;
use Raml\TypeInterface;

/**
 * ArrayType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class ArrayType extends Type
{
    /**
     * Scalar types which we can validate
     */
    private static $SCALAR_TYPES = [
        'integer',
        'string',
        'boolean',
        'number',
        'datetime-only',
        'date-only',
        'time-only',
    ];

    /**
     * Boolean value that indicates if items in the array MUST be unique.
     *
     * @var bool
     */
    private $uniqueItems;

    /**
     * Indicates the type all items in the array are inherited from. Can be a reference to an existing type or an inline type declaration.
     *
     * @var string|TypeInterface
     */
    private $items;

    /**
     * Minimum amount of items in array. Value MUST be equal to or greater than 0.
     * Default: 0.
     *
     * @var int
     */
    private $minItems = 0;

    /**
     * Maximum amount of items in array. Value MUST be equal to or greater than 0.
     * Default: 2147483647.
     *
     * @var int
     */
    private $maxItems = 2147483647;

    /**
     * Create a new ArrayType from an array of data
     *
     * @param string $name
     *
     * @return ArrayType
     */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        \assert($type instanceof self);
        $pos = \mb_strpos($type->getType(), '[]');
        if ($pos !== false) {
            $type->setItems(\mb_substr($type->getType(), 0, $pos));
        }
        $type->setType('array');

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'uniqueItems':
                    $type->setUniqueItems($value);

                    break;
                case 'items':
                    if (\is_array($value) && isset($value['type'])) {
                        $type->setItems($value['type']);

                        break;
                    }
                    $type->setItems($value);

                    break;
                case 'minItems':
                    $type->setMinItems($value);

                    break;
                case 'maxItems':
                    $type->setMaxItems($value);

                    break;
            }
        }

        return $type;
    }

    /**
     * @param bool $uniqueItems
     */
    public function setUniqueItems($uniqueItems): void
    {
        $this->uniqueItems = $uniqueItems;
    }

    /**
     * @param string $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }

    /**
     * Get the value of Items
     *
     * @return TypeInterface
     */
    public function getItems()
    {
        if (!($this->items instanceof TypeInterface)) {
            $this->items = TypeCollection::getInstance()->getTypeByName($this->items);
        }

        return $this->items;
    }

    /**
     * @param int $minItems
     */
    public function setMinItems($minItems): void
    {
        $this->minItems = $minItems;
    }

    /**
     * @param int $maxItems
     */
    public function setMaxItems($maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    public function validate($value): void
    {
        parent::validate($value);

        if (!\is_array($value)) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'is array', $value);

            return;
        }

        $actualArraySize = \count($value);
        if (!($actualArraySize >= $this->minItems && $actualArraySize <= $this->maxItems)) {
            $this->errors[] = TypeValidationError::arraySizeValidationFailed(
                $this->getName(),
                $this->minItems,
                $this->maxItems,
                $actualArraySize
            );
        }

        if (\in_array($this->items, self::$SCALAR_TYPES, true)) {
            $this->validateScalars($value);
        } else {
            $this->validateObjects($value);
        }
    }

    private function validateScalars($value): void
    {
        $typeObject = ApiDefinition::determineType('item', ['type' => $this->items]);

        foreach ($value as $valueItem) {
            $typeObject->validate($valueItem);
            if (!$typeObject->isValid()) {
                $this->errors[] = TypeValidationError::unexpectedArrayValueType(
                    $this->getName(),
                    $this->items,
                    $valueItem
                );
            }
        }
    }

    private function validateObjects($value): void
    {
        foreach ($value as $valueItem) {
            $this->getItems()->validate($valueItem);
            if (!$this->getItems()->isValid()) {
                $this->errors = \array_merge($this->errors, $this->getItems()->getErrors());
            }
        }
    }
}
