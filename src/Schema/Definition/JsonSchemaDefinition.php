<?php

namespace Raml\Schema\Definition;

use \Raml\Schema\SchemaDefinitionInterface;
use \JsonSchema\Validator;
use Raml\Types\TypeValidationError;

class JsonSchemaDefinition implements SchemaDefinitionInterface
{
    /**
     * The JSON schema
     *
     * @var \stdClass
     */
    private $json;

    private $errors = [];

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
     * @param $value
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validate($value)
    {
        $validator = new Validator();
        $jsonSchema = $this->json;

        $validator->check($value, $jsonSchema);

        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                $this->errors[] = new TypeValidationError($error['property'], $error['constraint']);
            }
        }
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

    /**
     * @return TypeValidationError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return empty($this->errors);
    }
}
