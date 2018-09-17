<?php

namespace Raml\Types;

use Raml\Type;

/**
 * FileType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class FileType extends Type
{
    /**
     * A list of valid content-type strings for the file. The file type * / * MUST be a valid value.
     *
     * @var array
     */
    private $fileTypes;

    /**
     * Specifies the minimum number of bytes for a parameter value. The value MUST be equal to or greater than 0.
     * Default: 0
     *
     * @var int
     */
    private $minLength;

    /**
     * Specifies the maximum number of bytes for a parameter value. The value MUST be equal to or greater than 0.
     * Default: 2147483647
     *
     * @var int
     */
    private $maxLength;

    /**
    * Create a new FileType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return FileType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        assert($type instanceof self);

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'fileTypes':
                    $type->setFileTypes($value);

                    break;
                case 'minLength':
                    $type->setMinLength($value);

                    break;
                case 'maxLength':
                    $type->setMaxLength($value);

                    break;
            }
        }

        return $type;
    }

    /**
     * Get the value of File Types
     *
     * @return mixed
     */
    public function getFileTypes()
    {
        return $this->fileTypes;
    }

    /**
     * Set the value of File Types
     *
     * @param mixed $fileTypes
     *
     * @return self
     */
    public function setFileTypes($fileTypes)
    {
        $this->fileTypes = $fileTypes;

        return $this;
    }

    /**
     * Get the value of Min Length
     *
     * @return mixed
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set the value of Min Length
     *
     * @param mixed $minLength
     *
     * @return self
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * Get the value of Max Length
     *
     * @return mixed
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set the value of Max Length
     *
     * @param mixed $maxLength
     *
     * @return self
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function validate($value)
    {
        parent::validate($value);

        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $value)) {
            $this->errors[] = TypeValidationError::unexpectedValueType($this->getName(), 'file', $value);
        }
    }
}
