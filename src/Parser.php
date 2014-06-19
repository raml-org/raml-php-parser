<?php
namespace Raml;

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
     * @param $fileName
     *
     * @return array
     */
    public function parse($fileName)
    {
        $rootDir = dirname($fileName);

        $array = $this->parseYaml($fileName);

        $array = $this->includeAndParseFiles($array, $rootDir);

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
                    $array[$key] = $this->replaceTypes($value, $keyedTraits, $key, $name);
                }
            }
        }

        // ---

        $array = $this->arrayMapRecursive(
            function ($data) use ($rootDir) {
                if (is_string($data) && $this->isJson($data)) {
                    $retriever = new \JsonSchema\Uri\UriRetriever;
                    $jsonSchemaParser = new \JsonSchema\RefResolver($retriever);

                    $data = json_decode($data);
                    $jsonSchemaParser->resolve($data, 'file:'.$rootDir.'/');

                    return $data;
                }

                return $data;

            },
            $array
        );

        return $array;
    }

    // ---

    /**
     * Checks if a string is JSON
     *
     * @param $string
     *
     * @return bool
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
     *
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
     * @param $fileName
     *
     * @return array
     */
    private function parseYaml($fileName)
    {
        return \Symfony\Component\Yaml\Yaml::parse($fileName);
    }

    /**
     * Convert a JSON Schema file into a stdClass
     *
     * @param $fileName
     *
     * @return \stdClass
     */
    private function parseJsonSchema($fileName)
    {
        $retriever = new \JsonSchema\Uri\UriRetriever;
        $jsonSchemaParser = new \JsonSchema\RefResolver($retriever);
        return $jsonSchemaParser->fetchRef('file://' . $fileName, null);
    }

    /**
     * Convert a RAML file into an array
     *
     * @param $fileName
     *
     * @return array
     */
    private function parseRaml($fileName)
    {
        return $this->parse($fileName);
    }

    /**
     * Load and parse a file
     *
     * @param $fileName
     * @param $rootDir
     *
     * @throws \Exception
     *
     * @return array|\stdClass
     */
    private function loadAndParseFile($fileName, $rootDir)
    {
        if (isset($this->cachedFiles[$fileName])) {
            return $this->cachedFiles[$fileName];
        }

        switch(pathinfo($fileName, PATHINFO_EXTENSION)) {
            case 'json':
                $fileData = $this->parseJsonSchema($rootDir. '/'.$fileName, null);
                break;
            case 'yaml':
            case 'yml':
                $fileData = $this->parseYaml($rootDir. '/'.$fileName);
                break;
            case 'raml':
            case 'rml':
                $fileData = $this->parseRaml($rootDir. '/'.$fileName);
                break;
            default:
                throw new \Exception('Extension "' . pathinfo($fileName, PATHINFO_EXTENSION) . '" not supported (yet)');
        }

        $this->cachedFiles[$fileName] = $fileData;
        return $fileData;
    }

    /**
     * Recurse through the structure and load includes
     *
     * @param $array
     * @param $rootDir
     *
     * @return array|\stdClass
     */
    private function includeAndParseFiles($array, $rootDir)
    {
        if (is_array($array)) {
            return array_map(
                function ($array) use ($rootDir) {
                    return $this->includeAndParseFiles($array, $rootDir);
                },
                $array
            );
        } elseif (strpos($array, '!include') === 0) {
            return $this->loadAndParseFile(str_replace('!include ', '', $array), $rootDir);
        } else {
            return $array;
        }
    }

    /**
     * Insert the traits into the RAML file
     *
     * @param $raml
     * @param $traits
     *
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
                    if (is_array($traitName)) {
                        $traitVariables = current($traitName);
                        $traitName = key($traitName);

                        $traitVariables['resourcePath'] = $path;
                        $traitVariables['resourcePathName'] = $name;

                        $trait = $this->applyTraitVariables($traitVariables, $traits[$traitName]);
                    } else {
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
     * Insert the traits into the RAML file
     *
     * @param $raml
     * @param $traits
     *
     * @return array
     */
    private function replaceTypes($raml, $traits, $path, $name)
    {
        if (!is_array($raml)) {
            return $raml;
        }

        $newArray = [];

        foreach ($raml as $key => $value) {
            if ($key === 'type') {
                if (is_array($value)) {
                    $traitVariables = current($value);
                    $traitName = key($value);

                    $traitVariables['resourcePath'] = $path;
                    $traitVariables['resourcePathName'] = $name;

                    $trait = $this->applyTraitVariables($traitVariables, $traits[$traitName]);
                } else {
                    $trait = $traits[$value];
                }
                $newArray = array_replace_recursive($newArray, $this->replaceTypes($trait, $traits, $path, $name));

            } else {
                $newValue = $this->replaceTypes($value, $traits, $path, $name);


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
     * @param $values
     * @param $trait
     *
     * @return mixed
     */
    private function applyTraitVariables($values, $trait)
    {

        $jsonString = json_encode($trait, true);

        foreach ($values as $key => $value) {
            $jsonString = str_replace('\u003C\u003C'.$key.'\u003E\u003E', $value, $jsonString);
        }

        return json_decode($jsonString, true);
    }
}
