<?php

namespace Raml\Schema\Definition;

use JsonSchema\Constraints\Constraint;
use Raml\Schema\SchemaDefinitionInterface;
use JsonSchema\Validator;
use Raml\Types\TypeValidationError;

final class JsonSchemaDefinition implements SchemaDefinitionInterface
{
    /**
     * The JSON schema
     *
     * @var \stdClass
     */
    private $json;

    private $errors = [];

    /**
     * Create a JSON Schema definition
     *
     * @param \stdClass $json
     */
    public function __construct(\stdClass $json)
    {
        $this->json = $json;
    }

    /**
     * Validate a JSON string against the schema
     * - Converts the string into a JSON object then uses the JsonSchema Validator to validate
     *
     * @param mixed $value
     */
    public function validate($value)
    {
        $validator = new Validator();
        $jsonSchema = $this->json;

        $validator->validate($value, $jsonSchema, Constraint::CHECK_MODE_TYPE_CAST);

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
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }
}
