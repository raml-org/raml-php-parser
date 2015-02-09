<?php

namespace Raml\Exception\BadParameter;

use Raml\Exception\RamlParserException;
use Raml\Exception\BadParameterExceptionInterface;

class ResourceNotFoundException extends RamlParserException implements BadParameterExceptionInterface
{
    protected $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;

        parent::__construct(sprintf('The URI %s does not exist.', $this->uri));
    }

    public function getUri()
    {
        return $this->uri;
    }
}
