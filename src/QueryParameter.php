<?php
namespace Raml;

class QueryParameter {
    private $description;
    private $type;

    public function __construct($description, $type='string') {
        $this->description = $description;
        $this->type = $type;
    }

    // ---

    public function getDescription()
    {
        return $this->description;
    }

    public function getType()
    {
        return $this->type;
    }
}