<?php
namespace Raml;

use JsonSchema\Uri\UriRetriever;
use JsonSchema\RefResolver;
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

    // ---

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

        // ---

        if ($parseSchemas && $array) {
            $array = $this->arrayMapRecursive(
                function ($data) use ($rootDir) {
                    if (is_string($data) && $this->isJson($data)) {
                        $retriever = new UriRetriever;
                        $jsonSchemaParser = new RefResolver($retriever);

                        $data = json_decode($data);
                        $jsonSchemaParser->resolve($data, 'file:' . $rootDir . '/');

                        return $data;
                    }

                    return $data;

                },
                $array
            );
        }

        return new ApiDefinition($array);
    }

    /**
     * Checks if a string is JSON
     *
     * @param string $string
     * @return boolean
     */
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Apply a callback to all elements of a recursive array
     *
     * @param callable $func
     * @param array $arr
     * @return array
     */
    private function arrayMapRecursive(callable $func, array $arr)
    {
        array_walk_recursive(
            $arr,
            function (&$v) use ($func) {
                $v = $func($v);
            }
        );

        return $arr;
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
     * Convert a JSON Schema file into a stdClass
     *
     * @param string $fileName
     * @return \stdClass
     */
    private function parseJsonSchema($fileName)
    {
        $retriever = new UriRetriever;
        $jsonSchemaParser = new RefResolver($retriever);
        try {
            return $jsonSchemaParser->fetchRef('file://' . $fileName, null);
        } catch (\Exception $e) {
            throw new \Exception('Invalid JSON in ' . $fileName);
        }
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

        if (in_array($fileExtension, ['yaml', 'yml', 'raml', 'rml'])) {
            // RAML and YAML files are always parsed
            $fileData = $this->includeAndParseFiles(
                $this->parseYaml($fullPath),
                dirname($fullPath),
                $parseSchemas
            );
        } elseif ($parseSchemas) {
            // Determine if we need to parse schemas
            switch ($fileExtension) {
                case 'json':
                    $fileData = $this->parseJsonSchema($fullPath, null);
                    break;
                default:
                    throw new \Exception('Extension "' . $fileExtension . '" not supported (yet)');
            }
        } else {
            // Or just include the string
            return file_get_contents($fullPath);
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
        $jsonString = json_encode($trait, true);

        $variables = implode('|', array_keys($values));

        $jsonString = preg_replace_callback('/\\\u003C\\\u003C(' . $variables . ')([\s]*\|[\s]*!(singularize|pluralize))?\\\u003E\\\u003E/', function($matches) use ($values) {
            $transformer = isset($matches[3]) ? $matches[3] : '';

            return method_exists('Inflect\Inflect', $transformer) ? Inflect::{$transformer}($values[$matches[1]]) : $values[$matches[1]];
        }, $jsonString);

        return json_decode($jsonString, true);
    }
}
