<?php
namespace Raml;

use Raml\Schema\SchemaParserInterface;
use Raml\Schema\Parser\JsonSchemaParser;
use Symfony\Component\Yaml\Yaml;
use Inflect\Inflect;

class Parser
{
    /**
     * Array of cached files
     * No point in fetching them twice
     *
     * @var array
     */
    private $cachedFiles = [];

    /**
     * List of schema parsers, keyed by the supported content type
     *
     * @var array
     */
    private $schemaParsers = [];

    // ---

    /**
     * Create a new parser object
     * - Optionally pass a list of parsers to use
     * - If null is passed then the default schemaParsers are used
     *
     * @param array $schemaParsers
     */
    public function __construct(array $schemaParsers = null)
    {
        // if null then use the default list
        if ($schemaParsers === null) {
            $schemaParsers = [
                new JsonSchemaParser()
            ];
        }

        // loop through each parser and add
        foreach ($schemaParsers as $schemaParser) {
            $this->addSchemaParser($schemaParser);
        }
    }

    /**
     * Add a new schema parser
     *
     * @param SchemaParserInterface $schemaParser
     */
    public function addSchemaParser(SchemaParserInterface $schemaParser)
    {
        foreach ($schemaParser->getCompatibleContentTypes() as $contentType) {
            $this->schemaParsers[$contentType] = $schemaParser;
        }
    }

    /**
     * Parse a RAML file
     *
     * @param string $fileName
     * @param boolean $parseSchemas
     *
     * @return \Raml\ApiDefinition
     */
    public function parse($fileName, $parseSchemas = true)
    {
        if (!is_file($fileName)) {
            throw new \Exception('File does not exist');
        }

        $rootDir = dirname(realpath($fileName));

        $array = $this->includeAndParseFiles(
            $this->parseYaml($fileName),
            $rootDir,
            $parseSchemas
        );

        if (!$array) {
            throw new \Exception('RAML file appears to be empty');
        }

        if (isset($array['traits'])) {
            $keyedTraits = [];
            foreach ($array['traits'] as $trait) {
                foreach ($trait as $k => $t) {
                    $keyedTraits[$k] = $t;
                }
            }

            foreach ($array as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $name = (isset($value['displayName'])) ? $value['displayName'] : substr($key, 1);
                    $array[$key] = $this->replaceTraits($value, $keyedTraits, $key, $name);
                }
            }
        }

        // ---

        if (isset($array['resourceTypes'])) {
            $keyedTraits = [];
            foreach ($array['resourceTypes'] as $trait) {
                foreach ($trait as $k => $t) {
                    $keyedTraits[$k] = $t;
                }
            }

            foreach ($array as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $name = (isset($value['displayName'])) ? $value['displayName'] : substr($key, 1);
                    $array[$key] = $this->replaceTypes($value, $keyedTraits, $key, $name, $key);
                }
            }
        }

        if ($parseSchemas) {
            $array = $this->recurseAndParseSchemas($array, $rootDir);
        }

        // ---

        return new ApiDefinition($array);
    }

    // ---

    /**
     * Recurses though the complete definition and replaces schema strings
     *
     * @param array  $array
     * @param string $rootDir
     *
     * @throws \Exception
     *
     * @return array
     */
    private function recurseAndParseSchemas($array, $rootDir)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                if (isset($value['schema'])) {
                    if (in_array($key, array_keys($this->schemaParsers))) {
                        $schemaParser = $this->schemaParsers[$key];
                        $schemaParser->setSourceUri('file:' . $rootDir . DIRECTORY_SEPARATOR);
                        $value['schema'] = $schemaParser->createSchemaDefinition($value['schema'], $rootDir);
                    } else {
                        throw new \Exception('Unknown schema type:'. $key);
                    }
                } else {
                    $value = $this->recurseAndParseSchemas($value, $rootDir);
                }
            }
        }


        return $array;
    }

    /**
     * Convert a yaml file into a string
     *
     * @param string $fileName
     * @return array
     */
    private function parseYaml($fileName)
    {
        return Yaml::parse($fileName);
    }

    /**
     * Load and parse a file
     *
     * @throws \Exception
     *
     * @param string $fileName
     * @param string $rootDir
     * @return array|\stdClass
     */
    private function loadAndParseFile($fileName, $rootDir, $parseSchemas)
    {
        // cache based on file name, prevents including/parsing the same file multiple times
        if (isset($this->cachedFiles[$fileName])) {
            return $this->cachedFiles[$fileName];
        }

        $fullPath = $rootDir . '/' . $fileName;
        if (is_readable($fullPath) === false) {
            return false;
        }

        $fileExtension = (pathinfo($fileName, PATHINFO_EXTENSION));

        $fileData = null;

        if (in_array($fileExtension, ['yaml', 'yml', 'raml', 'rml'])) {
            // RAML and YAML files are always parsed
            $fileData = $this->includeAndParseFiles(
                $this->parseYaml($fullPath),
                dirname($fullPath),
                $parseSchemas
            );
        } else {
            // Or just include the string
            $fileData = file_get_contents($fullPath);
        }

        // cache before returning
        $this->cachedFiles[$fileName] = $fileData;
        return $fileData;
    }

    /**
     * Recurse through the structure and load includes
     *
     * @param array|string $structure
     * @param string $rootDir
     * @return array|\stdClass
     */
    private function includeAndParseFiles($structure, $rootDir, $parseSchemas)
    {
        if (is_array($structure)) {
            return array_map(
                function ($structure) use ($rootDir, $parseSchemas) {
                    return $this->includeAndParseFiles($structure, $rootDir, $parseSchemas);
                },
                $structure
            );
        } elseif (strpos($structure, '!include') === 0) {
            return $this->loadAndParseFile(str_replace('!include ', '', $structure), $rootDir, $parseSchemas);
        } else {
            return $structure;
        }
    }

    /**
     * Insert the traits into the RAML file
     *
     * @param array $raml
     * @param array $traits
     * @param string $path
     * @param string $name
     * @return array
     */
    private function replaceTraits($raml, $traits, $path, $name)
    {
        if (!is_array($raml)) {
            return $raml;
        }

        $newArray = [];

        foreach ($raml as $key => $value) {
            if ($key === 'is') {
                foreach ($value as $traitName) {
                    $trait = [];
                    if (is_array($traitName)) {
                        $traitVariables = current($traitName);
                        $traitName = key($traitName);

                        $traitVariables['resourcePath'] = $path;
                        $traitVariables['resourcePathName'] = $name;

                        $trait = $this->applyTraitVariables($traitVariables, $traits[$traitName]);
                    } elseif (isset($traits[$traitName])) {
                        $trait = $traits[$traitName];
                    }
                    $newArray = array_replace_recursive($newArray, $this->replaceTraits($trait, $traits, $path, $name));
                }
            } else {
                $newValue = $this->replaceTraits($value, $traits, $path, $name);

                if (isset($newArray[$key])) {
                    $newArray[$key] = array_replace_recursive($newArray[$key], $newValue);
                } else {
                    $newArray[$key] = $newValue;
                }
            }

        }
        return $newArray;
    }

    /**
     * Insert the types into the RAML file
     *
     * @param array $raml
     * @param array $types
     * @param string $path
     * @param string $name
     * @return array
     */
    private function replaceTypes($raml, $types, $path, $name, $parentKey = null)
    {
        if (strpos($path, '/') !== 0 || !is_array($raml)) {
            return $raml;
        }

        $newArray = [];

        foreach ($raml as $key => $value) {
            if ($key === 'type' && strpos($parentKey, '/') === 0) {
                $type = [];

                $traitVariables = ['resourcePath' => $path, 'resourcePathName' => $name];

                if (is_array($value)) {
                    $traitVariables = array_merge($traitVariables, current($value));
                    $traitName = key($value);
                    $type = $this->applyTraitVariables($traitVariables, $types[$traitName]);
                } elseif (isset($types[$value])) {
                    $type = $this->applyTraitVariables($traitVariables, $types[$value]);
                }

                $newArray = array_replace_recursive($newArray, $this->replaceTypes($type, $types, $path, $name, $key));
            } else {
                $newValue = $this->replaceTypes($value, $types, $path, $name, $key);

                if (isset($newArray[$key])) {
                    $newArray[$key] = array_replace_recursive($newArray[$key], $newValue);
                } else {
                    $newArray[$key] = $newValue;
                }
            }

        }
        return $newArray;
    }

    /**
     * Add trait variables
     *
     * @param array $values
     * @param array $trait
     * @return mixed
     */
    private function applyTraitVariables(array $values, array $trait)
    {
        $variables = implode('|', array_keys($values));
        $newTrait = [];

        foreach ($trait as $key => &$value) {
            $newKey = preg_replace_callback(
                '/<<(' . $variables . ')([\s]*\|[\s]*!(singularize|pluralize))?>>/',
                function($matches) use ($values) {
                    $transformer = isset($matches[3]) ? $matches[3] : '';
                    return method_exists('Inflect\Inflect', $transformer) ?
                            Inflect::{$transformer}($values[$matches[1]]) :
                            $values[$matches[1]];
                },
                $key
            );

            if (is_array($value)) {
                $value = $this->applyTraitVariables($values, $value);
            } else {
                $value = preg_replace_callback(
                    '/<<(' . $variables . ')([\s]*\|[\s]*!(singularize|pluralize))?>>/',
                    function($matches) use ($values) {
                        $transformer = isset($matches[3]) ? $matches[3] : '';
                        return method_exists('Inflect\Inflect', $transformer) ?
                                Inflect::{$transformer}($values[$matches[1]]) :
                                $values[$matches[1]];
                    },
                    $value
                );
            }
            $newTrait[$newKey] = $value;
        }
        return $newTrait;
    }
}
