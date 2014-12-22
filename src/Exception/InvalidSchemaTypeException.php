<?php

namespace Raml\Exception;

use RuntimeException;

class InvalidSchemaTypeException extends RuntimeException implements ExceptionInterface
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;

        parent::__construct(sprintf('The key %s does not exist.', $this->type));
    }

    public function getType()
    {
        return $this->type;
    }
}
