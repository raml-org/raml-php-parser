<?php
namespace Raml;

use Raml\Exception\BadParameter\FileNotFoundException;
use Raml\Exception\InvalidSchemaTypeException;
use Raml\Exception\RamlParserException;

use Raml\Schema\SchemaParserInterface;
use Raml\Schema\Parser\JsonSchemaParser;

use Raml\SecurityScheme\SecuritySettingsParser\OAuth1SecuritySettingsParser;
use Raml\SecurityScheme\SecuritySettingsParser\OAuth2SecuritySettingsParser;
use Raml\SecurityScheme\SecuritySettingsParserInterface;

use Raml\FileLoader\DefaultFileLoader;
use Raml\FileLoader\JsonSchemaFileLoader;
use Raml\FileLoader\FileLoaderInterface;

use Symfony\Component\Yaml\Yaml;
use Inflect\Inflect;

/**
 * Converts a RAML file into a API Documentation tree
 */
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
     * @var SchemaParserInterface[]
     */
    private $schemaParsers = [];

    /**
     * List of security settings parsers
     *
     * @var SecuritySettingsParserInterface[]
     */
    private $securitySettingsParsers = [];

    /**
     * List of custom file loaders
     *
     * @var FileLoaderInterface[]
     */
    private $fileLoaders = [];

    // ---

    /**
     * Create a new parser object
     * - Optionally pass a list of parsers to use
     * - If null is passed then the default schemaParsers are used
     *
     * @param array $schemaParsers
     * @param array $securitySettingsParsers
     * @param array $fileLoaders
     */
    public function __construct(
        array $schemaParsers = null,
        array $securitySettingsParsers = null,
        array $fileLoaders = null
    ) {
        // ---
        // add schema parsers

        // if null then use a default list
        if ($schemaParsers === null) {
            $schemaParsers = [
                new JsonSchemaParser()
            ];
        }

        foreach ($schemaParsers as $schemaParser) {
            $this->addSchemaParser($schemaParser);
        }

        // ---
        // add security setting parsers

        // if null then use a default list
        if ($securitySettingsParsers === null) {
            $securitySettingsParsers = [
                new OAuth1SecuritySettingsParser(),
                new OAuth2SecuritySettingsParser()
            ];
        }

        foreach ($securitySettingsParsers as $securitySettingParser) {
            $this->addSecuritySettingParser($securitySettingParser);
        }

        // ---
        // add file loaders

        // if null then use a default list
        if ($fileLoaders === null) {
            $fileLoaders = [
                new JsonSchemaFileLoader(),
                new DefaultFileLoader()
            ];
        }

        foreach ($fileLoaders as $fileLoader) {
            $this->addFileLoader($fileLoader);
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
     * Add a new security scheme
     *
     * @param SecuritySettingsParserInterface $securitySettingsParser
     */
    public function addSecuritySettingParser(SecuritySettingsParserInterface $securitySettingsParser)
    {
        foreach ($securitySettingsParser->getCompatibleTypes() as $contentType) {
            $this->securitySettingsParsers[$contentType] = $securitySettingsParser;
        }
    }

    /**
     * Add a file loader
     *
     * @param FileLoaderInterface $fileLoader
     */
    public function addFileLoader(FileLoaderInterface $fileLoader)
    {
        foreach ($fileLoader->getValidExtensions() as $extension) {
            $this->fileLoaders[$extension] = $fileLoader;
        }
    }

    /**
     * Parse a RAML spec from a file
     *
     * @param string  $fileName
     * @param boolean $parseSchemas
     *
     * @throws FileNotFoundException
     * @throws RamlParserException
     *
     * @return \Raml\ApiDefinition
     */
    public function parse($fileName, $parseSchemas = true)
    {
        if (!is_file($fileName)) {
            throw new FileNotFoundException($fileName);
        }

        $rootDir = dirname($fileName);
        $ramlString = file_get_contents($fileName);

        $ramlData = $this->parseRamlString($ramlString, $rootDir, $parseSchemas);

        return $this->parseRamlData($ramlData, $rootDir, $parseSchemas);
    }

    /**
     * Parse a RAML spec from a string
     *
     * @param string  $ramlString
     * @param string  $rootDir
     * @param boolean $parseSchemas
     *
     * @throws RamlParserException
     *
     * @return \Raml\ApiDefinition
     */
    public function parseFromString($ramlString, $rootDir, $parseSchemas = true)
    {
        $ramlData = $this->parseRamlString($ramlString, $rootDir, $parseSchemas);

        return $this->parseRamlData($ramlData, $rootDir, $parseSchemas);
    }

    /**
     * Parse RAML data
     *
     * @param string  $ramlData
     * @param string  $rootDir
     * @param boolean $parseSchemas
     *
     * @throws RamlParserException
     *
     * @return \Raml\ApiDefinition
     */
    private function parseRamlData($ramlData, $rootDir, $parseSchemas = true)
    {
        if (!isset($ramlData['title'])) {
            throw new RamlParserException();
        }

        $ramlData = $this->parseTraits($ramlData);

        $ramlData = $this->parseResourceTypes($ramlData);

        if ($parseSchemas) {
            if (isset($ramlData['schemas'])) {
                $schemas = [];
                foreach ($ramlData['schemas'] as $schemaCollection) {
                    foreach($schemaCollection as $schemaName => $schema) {
                        $schemas[$schemaName] = $schema;
                    }
                }
            }
            foreach ($ramlData as $key => $value) {
                if (0 === strpos($key, '/')) {
                    if (isset($schemas)) {
                        $value = $this->replaceSchemas($value, $schemas);
                    }
                    if (is_array($value)) {
                        $value = $this->recurseAndParseSchemas($value, $rootDir);
                    }
                    $ramlData[$key] = $value;
                }
            }
        }


        if (isset($ramlData['securitySchemes'])) {
            $ramlData['securitySchemes'] = $this->parseSecuritySettings($ramlData['securitySchemes']);
        }

        return ApiDefinition::createFromArray($ramlData['title'], $ramlData);
    }

    /**
     * Replaces schema into the raml file
     *
     * @param  array $array
     * @param  array $schemas List of available schema definition
     * @return array
     */
    public function replaceSchemas($array, $schemas)
    {
        if (!is_array($array)) {
            return $array;
        }
        foreach ($array as $key => $value) {
            if ('schema' === $key) {
                if (isset($schemas[$value])) {
                    $array[$key] = $schemas[$value];
                }
            } else {
                $array[$key] = $this->replaceSchemas($value, $schemas);
            }
        }
        return $array;
    }

    /**
     * Recurses though resources and replaces schema strings
     *
     * @param array $array
     * @param string $rootDir
     *
     * @throws InvalidSchemaTypeException
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
                        throw new InvalidSchemaTypeException($key);
                    }
                } else {
                    $value = $this->recurseAndParseSchemas($value, $rootDir);
                }
            }
        }
        return $array;
    }

    /**
     * Parse the security settings
     *
     * @param $securitySchemes
     *
     * @return array
     */
    private function parseSecuritySettings($securitySchemes)
    {
        foreach ($securitySchemes as $key => $securityScheme) {
            if (isset($securityScheme['type']) && isset($this->securitySettingsParsers[$securityScheme['type']])) {
                $parser = $this->securitySettingsParsers[$securityScheme['type']];
                $securitySchemes[$key]['settings'] = $parser->createSecuritySettings($securityScheme['settings']);
            }
        }
        return $securitySchemes;

    }

    /**
     * Parse the resource types
     *
     * @param $ramlData
     *
     * @return array
     */
    private function parseResourceTypes($ramlData)
    {
        if (isset($ramlData['resourceTypes'])) {
            $keyedTraits = [];
            foreach ($ramlData['resourceTypes'] as $trait) {
                foreach ($trait as $k => $t) {
                    $keyedTraits[$k] = $t;
                }
            }

            foreach ($ramlData as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $name = (isset($value['displayName'])) ? $value['displayName'] : substr($key, 1);
                    $ramlData[$key] = $this->replaceTypes($value, $keyedTraits, $key, $name, $key);
                }
            }
        }

        return $ramlData;
    }

    /**
     * Parse the traits
     *
     * @param $ramlData
     *
     * @return array
     */
    private function parseTraits($ramlData)
    {
        if (isset($ramlData['traits'])) {
            $keyedTraits = [];
            foreach ($ramlData['traits'] as $trait) {
                foreach ($trait as $k => $t) {
                    $keyedTraits[$k] = $t;
                }
            }

            foreach ($ramlData as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $name = (isset($value['displayName'])) ? $value['displayName'] : substr($key, 1);
                    $ramlData[$key] = $this->replaceTraits($value, $keyedTraits, $key, $name);
                }
            }
        }

        // ---

        return $ramlData;
    }

    /**
     * Parse a RAML or YAML content
     *
     * @param string $ramlString
     * @param string  $rootDir
     * @param boolean $parseSchemas
     *
     * @throws \Exception
     *
     * @return array
     */
    private function parseRamlString($ramlString, $rootDir, $parseSchemas)
    {
        // get the header
        $header = strtok($ramlString, "\n");

        $data = $this->parseYaml($ramlString);

        if (!$data) {
            throw new \Exception('RAML file appears to be empty');
        }

        if (strpos($header, '#%RAML') === 0) {
            // @todo extract the vesion of the raml and do something with it

            $data = $this->includeAndParseFiles(
                $data,
                $rootDir,
                $parseSchemas
            );
        }

        return $data;
    }

    // ---

    /**
     * Convert a yaml string into an array
     *
     * @param string $fileData
     * @return array
     */
    protected function parseYaml($fileData)
    {
        return Yaml::parse($fileData, true, true);
    }

    /**
     * Load and parse a file
     *
     * @param string  $fileName
     * @param string  $rootDir
     * @param boolean $parseSchemas
     *
     * @throws \Exception
     *
     * @return array
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

        if (in_array($fileExtension, ['yaml', 'yml', 'raml'])) {
            // RAML and YAML files are always parsed
            $fileData = $this->parseRamlString(
                $fullPath,
                $rootDir,
                $parseSchemas
            );
            $fileData = $this->includeAndParseFiles($fileData, $rootDir, $parseSchemas);
        } else {
            if (in_array($fileExtension, array_keys($this->fileLoaders))) {
                $loader = $this->fileLoaders[$fileExtension];
            } else {
                $loader = $this->fileLoaders['*'];
            }

            $fileData = $loader->loadFile($fullPath);
        }

        // cache before returning
        $this->cachedFiles[$fileName] = $fileData;
        return $fileData;
    }

    /**
     * Recurse through the structure and load includes
     *
     * @param array|string $structure
     * @param string        $rootDir
     * @param boolean       $parseSchemas
     *
     * @return array
     */
    private function includeAndParseFiles($structure, $rootDir, $parseSchemas)
    {
        if (is_array($structure)) {
            $result = array();
            foreach ($structure as $key => $structureElement) {
                $result[$key] = $this->includeAndParseFiles($structureElement, $rootDir, $parseSchemas);
            }

            return $result;
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

                if (isset($newArray[$key]) && is_array($newArray[$key])) {
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
     * @param string $parentKey
     *
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

                if (isset($newArray[$key]) && is_array($newArray[$key])) {
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
                function ($matches) use ($values) {
                    $transformer = isset($matches[3]) ? $matches[3] : '';
                    switch($transformer) {
                        case 'singularize':
                            return Inflect::singularize($values[$matches[1]]);
                            break;
                        case 'pluralize':
                            return Inflect::pluralize($values[$matches[1]]);
                            break;
                        default:
                            return $values[$matches[1]];
                    }
                },
                $key
            );

            if (is_array($value)) {
                $value = $this->applyTraitVariables($values, $value);
            } else {
                $value = preg_replace_callback(
                    '/<<(' . $variables . ')([\s]*\|[\s]*!(singularize|pluralize))?>>/',
                    function ($matches) use ($values) {
                        $transformer = isset($matches[3]) ? $matches[3] : '';

                        switch($transformer) {
                            case 'singularize':
                                return Inflect::singularize($values[$matches[1]]);
                                break;
                            case 'pluralize':
                                return Inflect::pluralize($values[$matches[1]]);
                                break;
                            default:
                                return $values[$matches[1]];
                        }
                    },
                    $value
                );
            }
            $newTrait[$newKey] = $value;
        }
        return $newTrait;
    }
}
