<?php
namespace Raml;

class QueryParameter
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $description
     * @param string $type
     */
    public function __construct($description, $type = 'string')
    {
        $this->description = $description;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
