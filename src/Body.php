<?php

namespace Raml;

use Raml\Schema\SchemaDefinitionInterface;

use Raml\Exception\BadParameter\InvalidSchemaDefinitionException;
use Raml\Exception\MutuallyExclusiveElementsException;
use Raml\ApiDefinition;
use Raml\TypeInterface;
use Raml\Type\ObjectType;
use Raml\Type;

/**
 * A body
 *
 * @see http://raml.org/spec.html#body
 */
class Body implements BodyInterface, ArrayInstantiationInterface
{
    /**
     * The description of the method (optional)
     *
     * @see http://raml.org/spec.html#description
     *
     * @var string
     */
    private $description;

    /**
     * The media type of the body
     *
     * @see http://raml.org/spec.html#body
     *
     * @var string
     */
    private $mediaType;

    // --

    /**
     * The schema of the body
     *
     * @see http://raml.org/spec.html#schema
     *
     * @var SchemaDefinitionInterface|string
     */
    private $schema;

    /**
     * The type of the body
     *
     * @see http://raml.org/spec.html#raml-data-types
     *
     * @var SchemaDefinitionInterface|string
     */
    private $type;

    /**
     * A list of examples
     *
     * @see http://raml.org/spec.html#schema
     *
     * @var string[]
     */
    private $examples;

    // ---

    /**
     * Create a new body
     *
     * @param string $mediaType
     *
     * @throws InvalidSchemaDefinitionException
     */
    public function __construct($mediaType)
    {
        if (in_array($mediaType, WebFormBody::$validMediaTypes)) {
            throw new \Exception('Invalid media type');
        }

        $this->mediaType = $mediaType;
    }

    /**
     * Create a new body from an array
     *
     * @param string $mediaType
     * @param array $data
     * [
     *  type:       ?string
     *  schema:     ?string
     *  example:    ?string
     *  examples:   ?array
     * ]
     *
     * @throws \Exception
     *
     * @return Body
     */
    public static function createFromArray($mediaType, array $data = [])
    {
        $body = new static($mediaType);

        if (isset($data['description'])) {
            $body->setDescription($data['description']);
        }

        if (isset($data['schema']) && isset($data['type'])) {
            throw new MutuallyExclusiveElementsException();
        }

        if (isset($data['type'])) {
            $name = '';
            $definition = $data['type'];

            $type = ApiDefinition::determineType(ApiDefinition::ROOT_ELEMENT_NAME, $definition);
            if ($type instanceof ObjectType) {
                $type->inheritFromParent();
            }
            $body->setType($type);
        } else {
            // nothing defined means a default of the any type
            // see https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#determine-default-types
            $body->setType(new Type('default'));
        }

        if (isset($data['example'])) {
            $body->addExample($data['example']);
        }

        if (isset($data['examples'])) {
            foreach ($data['examples'] as $example) {
                $body->addExample($example);
            }
        }

        return $body;
    }

    /**
     * Get the media type
     *
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    // --

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // --

    /**
     * @deprecated Schema has been deprecated and is superseded by type.
     * Get the schema
     *
     * @return SchemaDefinitionInterface|string
     */
    public function getSchema()
    {
        return $this->type;
    }

    /**
     * @deprecated Schema has been deprecated and is superseded by type.
     * Set the schema
     *
     * @param SchemaDefinitionInterface|string $schema
     *
     * @throws \Exception
     */
    public function setSchema($schema)
    {
        if (!is_string($schema) && !$schema instanceof TypeInterface) {
            throw new InvalidSchemaDefinitionException();
        }

        $this->type = $schema;
    }

    // --

    /**
     * Get the type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type
     *
     * @param \Raml\TypeInterface $type
     *
     * @throws \Exception Throws exception when type does not parse
     */
    public function setType(TypeInterface $type)
    {
        $this->type = $type;
    }

    // --

    /**
     * Get the example
     *
     * @return string
     */
    public function getExample()
    {
        return $this->examples[0];
    }

    /**
     * Get the list of examples
     *
     * @return string[]
     */
    public function getExamples()
    {
        return $this->examples;
    }

    /**
     * Add an example
     *
     * @param string $example
     */
    public function addExample($example)
    {
        $this->examples[] = $example;
    }
}
