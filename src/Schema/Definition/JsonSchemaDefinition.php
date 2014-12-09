<?php

namespace Raml\Schema\Definition;

use \Raml\Schema\SchemaDefinitionInterface;

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

        // @todo Find a nice way of showing errors

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON: Error '.json_last_error());
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
     * @param $string
     *
     * @throws \Exception
     *
     * @return boolean
     */
    public function validateJsonObject($json)
    {
        $validator = new \JsonSchema\Validator();

        $validator->check($json, $this->json);

        if (!$validator->isValid()) {
            throw new \Exception(json_encode($validator->getErrors(), true));
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
        return json_decode(json_encode($this->json), true);
    }
}
