<?php

namespace Raml\Type;

use Raml\Type;
use JsonSchema\Validator;
use Raml\Exception\InvalidSchemaException;
use Raml\Exception\InvalidJsonException;

/**
 * JsonType class
 *
 * @author Melvin Loos <m.loos@infopact.nl>
 */
class JsonType extends Type
{
    /**
     * Json schema
     *
     * @var string
     **/
    private $json;

    /**
    * Create a new JsonType from an array of data
    *
    * @param string    $name
    * @param array     $data
    *
    * @return StringType
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);

        $json = $data['type'];

        $type->setJson($json);
        
        return $type;
    }

    public function setJson($json) {
        if (!is_string($json)) {
            throw new InvalidJsonException();
        }
        $this->json = $json;
    }

    /**
     * Validate a JSON string against the schema
     * - Converts the string into a JSON object then uses the JsonSchema Validator to validate
     *
     * @param string $string JSON object to validate.
     *
     * @return bool
     * @throws InvalidJsonException Thrown when string is invalid JSON.
     */
    public function validate($string)
    {
        if (is_string($json)) {
            $json = json_decode($string);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidJsonException(json_last_error());
            }
        } else {
            $json = (object) $string;
        }

        return $this->validateJsonObject($json);
    }

    /**
     * Validates a json object
     *
     * @param string $json
     *
     * @throws InvalidSchemaException Thrown when the string does not validate against the schema.
     *
     * @return bool
     */
    public function validateJsonObject($json)
    {
        $validator = new Validator();
        $jsonSchema = json_decode($this->json);

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
        return json_decode($this->json);
    }

    /**
     * Returns the JSON Schema as an array
     *
     * @return array
     */
    public function getJsonArray()
    {
        return json_decode($this->json, true);
    }

    /**
     * Returns the original JSON schema
     *
     * @return string
     */
    public function __toString()
    {
        return $this->json;
    }
}
