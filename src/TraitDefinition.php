<?php

namespace Raml;

use Raml\Utility\TraitParserHelper;

class TraitDefinition implements ArrayInstantiationInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var NamedParameter[]
     */
    private $queryParameters;

    /**
     * @var string
     */
    private $usage;

    /**
     * @var string
     */
    private $description;

    /**
     * @var NamedParameter[]
     */
    private $headers;

    /**
     * @var TraitDefinition[]
     */
    private $traits;

    /**
     * @var array
     */
    private $definition;

    /**
     * @param string $name
     */
    function __construct($name)
    {
        $this->name = $name;
        $this->queryParameters = [];
        $this->headers = [];
        $this->traits = [];
    }

    public static function createFromArray($name, array $data = [])
    {
        $class = new static($name);

        if (isset($data['queryParameters'])) {
            $queryParameters = [];
            foreach ($data['queryParameters'] as $key => $parameter) {
                $queryParameters[] = NamedParameter::createFromArray($key, $parameter);
            }
            $class->setQueryParameters($queryParameters);
        }
        if (isset($data['usage'])) {
            $class->setUsage($data['usage']);
        }
        if (isset($data['description'])) {
            $class->setDescription($data['description']);
        }
        if (isset($data['headers'])) {
            $headers = [];
            foreach ($data['headers'] as $key => $header) {
                $headers[] = NamedParameter::createFromArray($key, $header);
            }
            $class->setHeaders($headers);
        }
        if (isset($data['is'])) {
            $class->traits = (array)$data['is'];
        }

        $class->setDefinition($data);

        return $class;
    }

    public function toArray()
    {
        return $this->definition;
    }

    public function parseVariables(array $variables)
    {
        $definition = TraitParserHelper::applyVariables($variables, $this->getDefinition());
        return static::createFromArray($this->getName(), $definition);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return NamedParameter[]
     */
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }

    /**
     * @param NamedParameter[] $queryParameters
     *
     * @return $this
     */
    public function setQueryParameters($queryParameters)
    {
        $this->queryParameters = $queryParameters;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * @param string $usage
     *
     * @return $this
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return NamedParameter[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param NamedParameter[] $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return TraitDefinition[]
     */
    public function getTraits()
    {
        foreach ($this->traits as $key => $item) {
            if (!$item instanceof TraitDefinition) {
                $this->traits[$key] = TraitCollection::getInstance()->getTraitByName($item);
            }
        }

        return $this->traits;
    }

    /**
     * @param TraitDefinition[] $traits
     *
     * @return $this
     */
    public function setTraits($traits)
    {
        $this->traits = $traits;
        return $this;
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param array $definition
     *
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
        return $this;
    }
}
