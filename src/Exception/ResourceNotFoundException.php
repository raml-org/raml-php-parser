<?php

namespace Raml\Exception;

class ResourceNotFoundException extends RamlParserException implements ExceptionInterface
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