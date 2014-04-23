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

        if(isset($array['traits'])) {
            $keyedTraits = [];
            foreach($array['traits'] as $trait) {
                foreach($trait as $k=>$t) {
                    $keyedTraits[$k] = $t;
                }
            }

            $array = $this->replaceTraits($array, $keyedTraits);
        }


        return $array;
    }

    // ---

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
        if(isset($this->cachedFiles[$fileName])) {
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
                throw new \Exception(pathinfo('Extension "'.$fileName, PATHINFO_EXTENSION) . '" not supported (yet)');
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
        if(is_array($array)) {
            return array_map(function($array) use ($rootDir) {
                    return $this->includeAndParseFiles($array, $rootDir);
                }, $array);
        } elseif(strpos($array, '!include') === 0) {
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
    private function replaceTraits($raml, $traits)
    {
        if(!is_array($raml)) {
            return $raml;
        }

        $newArray = [];

        foreach ($raml as $key => $value) {
            if($key === 'is') {
                foreach($value as $traitName) {
                    $newArray = array_replace_recursive($newArray, $this->replaceTraits($traits[$traitName], $traits));
                }
            } else {
                $newValue = $this->replaceTraits($value, $traits);
                $newArray[$key] = isset($newArray[$key]) ? array_replace_recursive($newArray[$key], $newValue) : $newValue;

            }

        }
        return $newArray;
    }
}