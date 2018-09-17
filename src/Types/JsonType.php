<?php

namespace Raml\Types;

use Raml\Type;
use JsonSchema\Validator;

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
     * @var array
     */
    private $json;

    /**
    * Create a new JsonType from an array of data
    *
    * @param string $name
    * @param array $data
    * @return self
    */
    public static function createFromArray($name, array $data = [])
    {
        $type = parent::createFromArray($name, $data);
        assert($type instanceof self);

        $type->json = $data;

        return $type;
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

        $validator->check($value, $jsonSchema);

        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                $this->errors[] = new TypeValidationError($error['property'], $error['constraint']);
            }
        }
    }
}
