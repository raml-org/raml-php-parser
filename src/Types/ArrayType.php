<?php

namespace Raml\Types;

use Raml\Type;

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
     * @var string
     **/
    private $items;

    /**
     * Minimum amount of items in array. Value MUST be equal to or greater than 0.
     * Default: 0.
     *
     * @var int
     **/
    private $minItems;

    /**
     * Maximum amount of items in array. Value MUST be equal to or greater than 0.
     * Default: 2147483647.
     *
     * @var int
     **/
    private $maxItems;

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
     * Get the value of Unique Items
     *
     * @return bool
     */
    public function getUniqueItems()
    {
        return $this->uniqueItems;
    }

    /**
     * Set the value of Unique Items
     *
     * @param bool $uniqueItems
     *
     * @return self
     */
    public function setUniqueItems($uniqueItems)
    {
        $this->uniqueItems = $uniqueItems;

        return $this;
    }

    /**
     * Get the value of Items
     *
     * @return string
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set the value of Items
     *
     * @param string $items
     *
     * @return self
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get the value of Min Items
     *
     * @return int
     */
    public function getMinItems()
    {
        return $this->minItems;
    }

    /**
     * Set the value of Min Items
     *
     * @param int $minItems
     *
     * @return self
     */
    public function setMinItems($minItems)
    {
        $this->minItems = $minItems;

        return $this;
    }

    /**
     * Get the value of Max Items
     *
     * @return int
     */
    public function getMaxItems()
    {
        return $this->maxItems;
    }

    /**
     * Set the value of Max Items
     *
     * @param int $maxItems
     *
     * @return self
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $maxItems;

        return $this;
    }
}
