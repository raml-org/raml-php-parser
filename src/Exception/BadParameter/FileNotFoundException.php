<?php

namespace Raml\Exception\BadParameter;

use RuntimeException;
use Raml\Exception\ExceptionInterface;
use Raml\Exception\BadParameterExceptionInterface;

class FileNotFoundException extends RuntimeException implements ExceptionInterface, BadParameterExceptionInterface
{
    protected $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;

        parent::__construct(sprintf('The file %s does not exist or is unreadable.', $this->fileName));
    }

    public function getFileName()
    {
        return $this->fileName;
    }
}
