<?php
namespace Raml;

use Inflect\Inflect;
use Raml\Exception\BadParameter\FileNotFoundException;
use Raml\Exception\InvalidSchemaFormatException;
use Raml\Exception\RamlParserException;
use Raml\FileLoader\DefaultFileLoader;
use Raml\FileLoader\FileLoaderInterface;
use Raml\FileLoader\JsonSchemaFileLoader;
use Raml\Schema\Parser\JsonSchemaParser;
use Raml\Schema\Parser\XmlSchemaParser;
use Raml\Schema\SchemaParserInterface;
use Raml\SecurityScheme\SecuritySettingsParser\DefaultSecuritySettingsParser;
use Raml\SecurityScheme\SecuritySettingsParser\OAuth1SecuritySettingsParser;
use Raml\SecurityScheme\SecuritySettingsParser\OAuth2SecuritySettingsParser;
use Raml\SecurityScheme\SecuritySettingsParserInterface;
use Symfony\Component\Yaml\Yaml;

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
     * @var array
     */
    private $cachedFilesPaths = [];

    /**
     * @var array
     */
    private $includedFiles = [];

    /**
     * List of schema parsers, keyed by the supported content type
     *
     * @var SchemaParserInterface[]
     */
    private $schemaParsers = [];

    /**
     * List of types
     *
     * @var string
     **/
    private $types = [];

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

    /**
     * Create a new parser object
     * - Optionally pass a list of parsers to use
     * - If null is passed then the default schemaParsers are used
     *
     * @param SchemaParserInterface[]           $schemaParsers
     * @param SecuritySettingsParserInterface[] $securitySettingsParsers
     * @param FileLoaderInterface[]             $fileLoaders
     * @param ParseConfiguration                $config
     */
    public function __construct(
        array $schemaParsers = null,
        array $securitySettingsParsers = null,
        array $fileLoaders = null,
        ParseConfiguration $config = null
    ) {
        // ---
        // parse settings
        $this->configuration = $config ?: new ParseConfiguration();

        // ---
        // add schema parsers

        // if null then use a default list
        if ($schemaParsers === null) {
            $schemaParsers = [
                new JsonSchemaParser(),
                new XmlSchemaParser()
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
                new OAuth2SecuritySettingsParser(),
                new DefaultSecuritySettingsParser()
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
     * Set the parse configuration
     *
     * @param ParseConfiguration $config
     */
    public function setConfiguration(ParseConfiguration $config)
    {
        $this->configuration = $config;
    }

    // ---

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
     * Add a new type
     *
     * @param TypeInterface $type Type to add.
     **/
    public function addType(TypeInterface $type)
    {
        $this->types[$type->getName()] = $type;
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

    // ---

    /**
     * Parse a RAML spec from a file
     *
     * @param string $rawFileName
     *
     * @throws FileNotFoundException
     * @throws RamlParserException
     *
     * @return \Raml\ApiDefinition
     */
    public function parse($rawFileName)
    {
        $fileName = realpath($rawFileName);

        if (!is_file($fileName)) {
            throw new FileNotFoundException($rawFileName);
        }

        $rootDir = dirname($fileName);
        $ramlString = file_get_contents($fileName);

        $ramlData = $this->parseRamlString($ramlString, $rootDir);

        return $this->parseRamlData($ramlData, $rootDir);
    }

    /**
     * Parse a RAML spec from a string
     *
     * @param string $ramlString
     * @param string $rootDir
     *
     * @throws RamlParserException
     *
     * @return \Raml\ApiDefinition
     */
    public function parseFromString($ramlString, $rootDir)
    {
        $ramlData = $this->parseRamlString($ramlString, $rootDir);

        return $this->parseRamlData($ramlData, $rootDir);
    }

    // ---

    /**
     * Parse RAML data
     *
     * @param string $ramlData
     * @param string $rootDir
     *
     * @throws RamlParserException
     *
     * @return \Raml\ApiDefinition
     */
    private function parseRamlData($ramlData, $rootDir)
    {
        if (!isset($ramlData['title'])) {
            throw new RamlParserException();
        }

        $ramlData = $this->parseTraits($ramlData);

        $ramlData = $this->parseResourceTypes($ramlData);

        if ($this->configuration->isSchemaParsingEnabled()) {
            if (isset($ramlData['schemas'])) {
                $schemas = [];
                foreach ($ramlData['schemas'] as $schemaCollection) {
                    foreach ($schemaCollection as $schemaName => $schema) {
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
     * @param  array $schemas List of available schema definition.
     *
     * @return array
     */
    private function replaceSchemas($array, $schemas)
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
     * @param array  $array
     * @param string $rootDir
     *
     * @throws InvalidSchemaFormatException
     *
     * @return array
     */
    private function recurseAndParseSchemas(array $array, $rootDir)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                if (isset($value['schema'])) {
                    $fileDir = $this->getCachedFilePath($value['schema']);
                    $schema = null;
                    foreach ($this->schemaParsers as $schemaParser) {
                        try {
                            $schemaParser->setSourceUri(
                                'file://' . ($fileDir ? $fileDir : $rootDir . DIRECTORY_SEPARATOR)
                            );
                            $schema = $schemaParser->createSchemaDefinition($value['schema']);

                            break;
                        } catch (\RuntimeException $e) {
                        }
                    }

                    if ($schema === null) {
                        throw new InvalidSchemaFormatException();
                    }

                    $value['schema'] = $schema;
                } else {
                    $value = $this->recurseAndParseSchemas($value, $rootDir);
                }
            }
        }

        return $array;
    }

    /**
     * @param string $data
     * @return string
     */
    private function getCachedFilePath($data) {
        $key = md5($data);

        return array_key_exists($key, $this->cachedFilesPaths) ? $this->cachedFilesPaths[$key] : null;
    }

    /**
     * Parse the security settings data into an array
     *
     * @param array $schemesArray
     *
     * @return array
     */
    private function parseSecuritySettings($schemesArray)
    {
        $securitySchemes = [];

        foreach ($schemesArray as $securitySchemeData) {
            // Create the default parser.
            if (isset($this->securitySettingsParsers['*'])) {
                $parser = $this->securitySettingsParsers['*'];
            } else {
                $parser = false;
            }
            // RAML spec defines a list of one security type per scheme
            if (count($securitySchemeData) == 1) {
                $key = key($securitySchemeData);
                $securitySchemes[$key] = $securitySchemeData[$key];
                $securityScheme = $securitySchemes[$key];

                // If we're using protocol specific parsers, see if we have one to use.
                if ($this->configuration->isSchemaSecuritySchemeParsingEnabled()) {
                    if (isset($securityScheme['type']) &&
                        isset($this->securitySettingsParsers[$securityScheme['type']])
                    ) {
                        $parser = $this->securitySettingsParsers[$securityScheme['type']];
                    }
                }

                // If we found a parser, create it's settings object.
                if ($parser) {
                    $settings = isset($securityScheme['settings']) ? $securityScheme['settings'] : [];
                    $securitySchemes[$key]['settings'] = $parser->createSecuritySettings($settings);
                }
            }
        }

        return $securitySchemes;
    }

    /**
     * Parse the resource types
     *
     * @param mixed $ramlData
     *
     * @return array
     */
    private function parseResourceTypes($ramlData)
    {
        if (isset($ramlData['resourceTypes'])) {
            $keyedResourceTypes = [];
            foreach ($ramlData['resourceTypes'] as $resourceType) {
                foreach ($resourceType as $k => $t) {
                    $keyedResourceTypes[$k] = $t;
                }
            }

            foreach ($ramlData as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $name = (isset($value['displayName'])) ? $value['displayName'] : substr($key, 1);
                    $ramlData[$key] = $this->replaceTypes($value, $keyedResourceTypes, $key, $name, $key);
                }
            }
        }

        return $ramlData;
    }

    /**
     * Parse the traits
     *
     * @param mixed $ramlData
     *
     * @return array
     */
    private function parseTraits($ramlData)
    {
        if (isset($ramlData['traits'])) {
            $keyedTraits = [];
            foreach ($ramlData['traits'] as $key => $trait) {
                if (is_int($key)) {
                    foreach ($trait as $k => $t) {
                        $keyedTraits[$k] = $t;
                    }
                } else {
                    foreach ($trait as $k => $t) {
                        $keyedTraits[$key][$k] = $t;
                    }
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
     * @param string $rootDir
     *
     * @throws \Exception
     *
     * @return array
     */
    private function parseRamlString($ramlString, $rootDir)
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
                $rootDir
            );
        }

        return $data;
    }

    // ---

    /**
     * Convert a yaml string into an array
     *
     * @param string $fileData
     *
     * @return array
     */
    private function parseYaml($fileData)
    {
        return Yaml::parse($fileData, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE & Yaml::PARSE_OBJECT);
    }

    /**
     * Load and parse a file
     *
     * @param string $fileName
     * @param string $rootDir
     *
     * @throws \Exception
     *
     * @return array
     */
    private function loadAndParseFile($fileName, $rootDir)
    {
        $rootDir = realpath($rootDir);
        $fullPath = realpath($rootDir . '/' . $fileName);

        if (is_readable($fullPath) === false) {
            throw new FileNotFoundException($fileName);
        }

        // Prevent LFI directory traversal attacks
        if (!$this->configuration->isDirectoryTraversalAllowed() &&
            substr($fullPath, 0, strlen($rootDir)) !== $rootDir
        ) {
            throw new FileNotFoundException($fileName);
        }

        $cacheKey = md5($fullPath);

        // cache based on file name, prevents including/parsing the same file multiple times
        if (isset($this->cachedFiles[$cacheKey])) {
            return $this->cachedFiles[$cacheKey];
        }

        $fileExtension = (pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, ['yaml', 'yml', 'raml'])) {
            $rootDir = dirname($rootDir . '/' . $fileName);

            // RAML and YAML files are always parsed
            $fileData = $this->parseRamlString(
                file_get_contents($fullPath),
                $rootDir
            );
            $fileData = $this->includeAndParseFiles($fileData, $rootDir);
        } else {
            if (in_array($fileExtension, array_keys($this->fileLoaders))) {
                $loader = $this->fileLoaders[$fileExtension];
            } else {
                $loader = $this->fileLoaders['*'];
            }

            $fileData = $loader->loadFile($fullPath);
            $this->cachedFilesPaths[md5($fileData)] = $fullPath;
        }

        // cache before returning
        $this->cachedFiles[$cacheKey] = $fileData;

        $this->includedFiles[] = $fullPath;

        return $fileData;
    }

    /**
     * Recurse through the structure and load includes
     *
     * @param array|string $structure
     * @param string       $rootDir
     *
     * @return array
     */
    private function includeAndParseFiles($structure, $rootDir)
    {
        if (is_array($structure)) {
            $result = array();
            foreach ($structure as $key => $structureElement) {
                $result[$key] = $this->includeAndParseFiles($structureElement, $rootDir);
            }

            return $result;
        } elseif (strpos($structure, '!include') === 0) {
            return $this->loadAndParseFile(str_replace('!include ', '', $structure), $rootDir);
        } else {
            return $structure;
        }
    }

    /**
     * Insert the traits into the RAML file
     *
     * @param string|array  $raml
     * @param array  $traits
     * @param string $path
     * @param string $name
     *
     * @return array
     */
    private function replaceTraits($raml, array $traits, $path, $name)
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

                        $trait = $this->applyVariables($traitVariables, $traits[$traitName]);
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
     * @param array  $raml
     * @param array  $types
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

                $typeVariables = ['resourcePath' => $path, 'resourcePathName' => $name];

                if (is_array($value)) {
                    $typeVariables = array_merge($typeVariables, current($value));
                    $typeName = key($value);
                    $type = $this->applyVariables($typeVariables, $types[$typeName]);
                } elseif (isset($types[$value])) {
                    $type = $this->applyVariables($typeVariables, $types[$value]);
                }

                $newArray = array_replace_recursive($newArray, $this->replaceTypes($type, $types, $path, $name, $key));
            } else {
                $newName = $name;
                if (strpos($key, '/') === 0 && !preg_match('/^\/\{.+\}$/', $key)) {
                    $newName = (isset($value['displayName'])) ? $value['displayName'] : substr($key, 1);
                }
                $newValue = $this->replaceTypes($value, $types, $path, $newName, $key);

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
     * Add trait/type variables
     *
     * @param array $values
     * @param array $trait
     *
     * @return mixed
     */
    private function applyVariables(array $values, array $trait)
    {
        $newTrait = [];

        foreach ($trait as $key => &$value) {
            $newKey = $this->applyFunctions($key, $values);

            if (is_array($value)) {
                $value = $this->applyVariables($values, $value);
            } else {
                $value = $this->applyFunctions($value, $values);
            }
            $newTrait[$newKey] = $value;
        }

        return $newTrait;
    }

    /**
     * Applies functions on variable if they are defined
     *
     * @param mixed $trait
     * @param array $values
     *
     * @return mixed    Return input $trait after applying functions (if any)
     */
    private function applyFunctions($trait, array $values)
    {
        $variables = implode('|', array_keys($values));
        return preg_replace_callback(
            '/<<(' . $variables . ')'.
            '('.
                '[\s]*\|[\s]*!'.
                '('.
                    'singularize|pluralize|uppercase|lowercase|lowercamelcase|uppercamelcase|lowerunderscorecase|upperunderscorecase|lowerhyphencase|upperhyphencase'.
                ')'.
            ')?>>/',
            function ($matches) use ($values) {
                $transformer = isset($matches[3]) ? $matches[3] : '';
                switch ($transformer) {
                    case 'singularize':
                        return Inflect::singularize($values[$matches[1]]);
                        break;
                    case 'pluralize':
                        return Inflect::pluralize($values[$matches[1]]);
                        break;
                    case 'uppercase':
                        return strtoupper($values[$matches[1]]);
                        break;
                    case 'lowercase':
                        return strtolower($values[$matches[1]]);
                        break;
                    case 'lowercamelcase':
                        return StringTransformer::convertString($values[$matches[1]], StringTransformer::LOWER_CAMEL_CASE);
                        break;
                    case 'uppercamelcase':
                        return StringTransformer::convertString($values[$matches[1]], StringTransformer::UPPER_CAMEL_CASE);
                        break;
                    case 'lowerunderscorecase':
                        return StringTransformer::convertString($values[$matches[1]], StringTransformer::LOWER_UNDERSCORE_CASE);
                        break;
                    case 'upperunderscorecase':
                        return StringTransformer::convertString($values[$matches[1]], StringTransformer::UPPER_UNDERSCORE_CASE);
                        break;
                    case 'lowerhyphencase':
                        return StringTransformer::convertString($values[$matches[1]], StringTransformer::LOWER_HYPHEN_CASE);
                        break;
                    case 'upperhyphencase':
                        return StringTransformer::convertString($values[$matches[1]], StringTransformer::UPPER_HYPHEN_CASE);
                        break;
                    default:
                        return $values[$matches[1]];
                }
            },
            $trait
        );
    }

    public function getIncludedFiles(): array
    {
        return $this->includedFiles;
    }
}
