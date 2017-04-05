<?php

namespace Raml\Types;

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
     * Boolean value that indicates if items in the array MUST be unique.
     *
     * @var bool
     **/
    private $uniqueItems;

    /**
     * Indicates the type all items in the array are inherited from. Can be a reference to an existing type or an inline type declaration.
     *
     * @var string|TypeInterface
     **/
    private $items;

    /**
     * Minimum amount of items in array. Value MUST be equal to or greater than 0.
     * Default: 0.
     *
     * @var int
     **/
    private $minItems = 0;

    /**
     * Maximum amount of items in array. Value MUST be equal to or greater than 0.
     * Default: 2147483647.
     *
     * @var int
     **/
    private $maxItems = 2147483647;

    /**
    * Create a new ArrayType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return ArrayType
    */
    public static function createFromArray($name, array $data = [])
    {
        /** @var ArrayType $type */
        $type = parent::createFromArray($name, $data);
        $pos = strpos($type->getType(), '[]');
        if ($pos !== false) {
            $type->setItems(substr($type->getType(), 0, $pos));
        }
        $type->setType('array');

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'uniqueItems':
                    $type->setUniqueItems($value);
                    break;
                case 'items':
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
    public function setUniqueItems($uniqueItems)
    {
        $this->uniqueItems = $uniqueItems;
    }

    /**
     * @param string $items
     */
    public function setItems($items)
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
    public function setMinItems($minItems)
    {
        $this->minItems = $minItems;
    }

    /**
     * @param int $maxItems
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $maxItems;
    }

    public function validate($value)
    {
        parent::validate($value);

        if (!is_array($value)) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'is array', $value);
            return;
        }

        $actualArraySize = count($value);
        if (!($actualArraySize >= $this->minItems && $actualArraySize <= $this->maxItems)) {
            $this->errors[] = TypeValidationError::arraySizeValidationFailed(
                $this->getName(),
                $this->minItems,
                $this->maxItems,
                $actualArraySize
            );
        }

        foreach ($value as $valueItem) {
            $this->getItems()->validate($valueItem);
            if (!$this->getItems()->isValid()) {
                $this->errors = array_merge($this->errors, $this->getItems()->getErrors());
            }
        }
    }
}
