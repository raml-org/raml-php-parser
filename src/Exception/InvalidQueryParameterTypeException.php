<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidQueryParameterTypeException extends RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $validTypes;

    public function __construct($type, array $validTypes)
    {
        $this->type = $type;
        $this->validTypes = $validTypes;

        parent::__construct(sprintf('The type %s is not a valid query parameter type.', $this->type));
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValidTypes()
    {
        return $this->validTypes;
    }
}
