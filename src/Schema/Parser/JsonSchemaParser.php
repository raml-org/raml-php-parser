<?php

namespace Raml\Schema\Parser;

use JsonSchema\SchemaStorage;
use Raml\Exception\InvalidJsonException;
use Raml\Schema\SchemaParserAbstract;
use Raml\Schema\Definition\JsonSchemaDefinition;

class JsonSchemaParser extends SchemaParserAbstract
{
    /**
     * List of known JSON content types
     *
     * @var string[]
     */
    protected $compatibleContentTypes = [
        'application/json',
        'text/json',
    ];

    /**
     * Create a new JSON Schema definition from a string
     *
     * @param string $schemaString
     * @return JsonSchemaDefinition
     *
     * @throws InvalidJsonException
     */
    public function createSchemaDefinition($schemaString)
    {
        $schemaStorage = new SchemaStorage();

        $schemaStorage->addSchema($this->getSourceUri(), json_decode($schemaString));
        $data = $schemaStorage->getSchema($this->getSourceUri());
        $data = $this->resolveRefSchemaRecursively($data, $schemaStorage);

        return new JsonSchemaDefinition($data);
    }

    /**
     * @param \stdClass|string $data
     * @param SchemaStorage $schemaStorage
     * @return mixed
     */
    private function resolveRefSchemaRecursively($data, SchemaStorage $schemaStorage)
    {
        $data = $schemaStorage->resolveRefSchema($data);
        if (!is_object($data) || (is_object($data) && !$data instanceof \stdClass)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $data->{$key} = $this->resolveRefSchemaRecursively($value, $schemaStorage);
            }

            if (is_array($value)) {
                $data->{$key} = array_map(function ($val) use ($schemaStorage) {
                    return $this->resolveRefSchemaRecursively($val, $schemaStorage);
                }, $value);
            }
        }

        return $data;
    }
}
