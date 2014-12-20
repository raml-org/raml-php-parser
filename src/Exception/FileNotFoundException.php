<?php

namespace Raml\Exception;

use RuntimeException;

class FileNotFoundException extends RuntimeException implements ExceptionInterface
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