<?php
namespace Raml;

use Raml\Exception\InvalidQueryParameterTypeException;

class QueryParameter
{
    private $validTypes = ['string', 'number', 'integer', 'date', 'boolean', 'file'];

    // ---

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $example;

    /**
     * @var boolean
     */
    private $required = false;

    // ---

    /**
     * Create a new Query Parameter
     * @param string  $description
     * @param string  $type
     * @param string  $displayName
     * @param strine  $example
     * @param boolean $required
     *
     * @throws \Exception
     */
    public function __construct($description, $type = 'string', $displayName = null, $example = null, $required = false)
    {
        if (!in_array($type, $this->validTypes)) {
            throw new InvalidQueryParameterTypeException($type, $this->validTypes);
        }

        $this->displayName = $displayName;
        $this->description = $description;
        $this->type = $type;
        $this->example = $example;
        $this->required = (bool) $required;
    }

    // ---

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
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

    /**
     * @return string
     */
    public function getExample()
    {
        return $this->example;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }
}
