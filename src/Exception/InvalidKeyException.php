<?php

namespace Raml\Exception;

class InvalidKeyException extends RamlParserException
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;

        parent::__construct(sprintf('The key %s does not exist.', $this->key));
    }

    public function getKey()
    {
        return $this->key;
    }
}
