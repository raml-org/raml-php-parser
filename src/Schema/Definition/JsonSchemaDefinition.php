<?php

namespace Raml\Schema\Definition;

use Raml\Exception\InvalidJsonException;
use Raml\Exception\InvalidSchemaException;
use \Raml\Schema\SchemaDefinitionInterface;
use \JsonSchema\Validator;

class JsonSchemaDefinition implements SchemaDefinitionInterface
{
    /**
     * The JSON schema
     *
     * @var \stdClass
     */
    private $json;

    // --

    /**
     * Create a JSON Schema definition
     *
     * @param \stdClass $json
     */
    public function __construct(\stdClass $json)
    {
        $this->json = $json;
    }

    // ---
    // SchemaDefinitionInterface

    /**
     * Validate a JSON string against the schema
     * - Converts the string into a JSON object then uses the JsonSchema Validator to validate
     *
     * @param $string
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function validate($string)
    {
        $json = json_decode($string);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidJsonException(json_last_error());
        }

        return $this->validateJsonObject($json);
    }

    /**
     * Returns the JSON schema as a string
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->json);
    }

    // ---

    /**
     * Validates a json object
     *
     * @param string $json
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function validateJsonObject($json)
    {
        $validator = new Validator();
        $jsonSchema = $this->json;

        $validator->check($json, $jsonSchema);

        if (!$validator->isValid()) {
            throw new InvalidSchemaException($validator->getErrors());
        }

        return true;
    }

    /**
     * Returns the JSON Schema as a \stdClass
     *
     * @return \stdClass
     */
    public function getJsonObject()
    {
        return $this->json;
    }

    /**
     * Returns the JSON Schema as an array
     *
     * @return array
     */
    public function getJsonArray()
    {
        $jsonSchema = $this->json;
        return json_decode(json_encode($jsonSchema), true);
    }
}
