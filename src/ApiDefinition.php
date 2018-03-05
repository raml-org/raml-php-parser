<?php
namespace Raml;

use Raml\RouteFormatter\RouteFormatterInterface;
use Raml\RouteFormatter\NoRouteFormatter;

use Raml\Schema\SchemaDefinitionInterface;
use Raml\RouteFormatter\BasicRoute;

use Raml\Exception\InvalidKeyException;
use Raml\Exception\BadParameter\ResourceNotFoundException;
use Raml\Exception\BadParameter\InvalidSchemaDefinitionException;
use Raml\Exception\BadParameter\InvalidProtocolException;
use Raml\Exception\MutuallyExclusiveElementsException;

use Raml\Utility\StringTransformer;

use Raml\Types\UnionType;
use Raml\Types\ArrayType;
use Raml\Types\LazyProxyType;

/**
 * The API Definition
 *
 * @see http://raml.org/spec.html
 */
class ApiDefinition implements ArrayInstantiationInterface
{
    const PROTOCOL_HTTP = 'HTTP';
    const PROTOCOL_HTTPS = 'HTTPS';

    /**
     * The API Title (required)
     *
     * @see http://raml.org/spec.html#api-title
     *
     * @var string
     */
    private $title;

    /**
     * The API Version (optional)
     *
     * @see http://raml.org/spec.html#api-version
     *
     * @var string
     */
    private $version;

    /**
     * The Base URL (optional for development, required in production)
     *
     * @see http://raml.org/spec.html#base-uri-and-baseuriparameters
     *
     * @var string
     */
    private $baseUri;

    /**
     * Parameters defined in the Base URI
     * - There appears to be a bug in the RAML 0.8 spec related to this,
     * however the baseUriParameters appears to be correct
     *
     * @see http://raml.org/spec.html#base-uri-and-baseuriparameters
     * @see http://raml.org/spec.html#uri-parameters
     *
     * @var NamedParameter[]
     */
    private $baseUriParameters = [];

    /**
     * The supported protocols (default to protocol on baseUrl)
     *
     * @see http://raml.org/spec.html#protocols
     *
     * @var array
     */
    private $protocols = [];

    /**
     * The default media type (optional)
     * - text/yaml
     * - text/x-yaml
     * - application/yaml
     *  - application/x-yaml
     *  - Any type from the list of IANA MIME Media Types, http://www.iana.org/assignments/media-types
     *  - A custom type that conforms to the regular expression, "application\/[A-Za-z.-0-1]*+?(json|xml)"
     *
     * @see http://raml.org/spec.html#default-media-type
     *
     * @var string[]
     */
    private $defaultMediaTypes;

    /**
     * The schemas the API supplies defined in the root (optional)
     *
     * @deprecated Replaced by types element.
     * @see http://raml.org/spec.html#schemas
     *
     * @var array[]
     */
    private $schemaCollections = [];

    /**
     * The documentation for the API (optional)
     *
     * @see http://raml.org/spec.html#user-documentation
     *
     * @var array
     */
    private $documentationList;

    /**
     * The resources the API supplies
     * {/*}
     *
     * @see http://raml.org/spec.html#resources-and-nested-resources
     *
     * @var \Raml\Resource[]
     */
    private $resources = [];

    /**
     * A list of security schemes
     *
     * @see http://raml.org/spec.html#declaration
     *
     * @var SecurityScheme[]
     */
    private $securitySchemes = [];

    /**
     * A list of security schemes that the whole API is secured by
     *
     * @link http://raml.org/spec.html#usage-applying-a-security-scheme-to-an-api
     *
     * @var SecurityScheme[]
     */
    private $securedBy = [];

    /**
     * A list of data types
     *
     * @link https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/#raml-data-types
     *
     * @var \Raml\TypeCollection
     */
    private $types = null;

    // ---

    /**
     * Create a new API Definition
     *
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
        $this->types = TypeCollection::getInstance();
        // since the TypeCollection is a singleton, we need to clear it for every parse
        $this->types->clear();
    }

    /**
     * Create a new API Definition from an array
     *
     * @param string $title
     * @param array  $data
     * [
     *  title:              string
     *  version:            ?string
     *  baseUrl:            ?string
     *  baseUriParameters:  ?array
     *  protocols:          ?array
     *  defaultMediaType:   ?string
     *  schemas:            ?array
     *  securitySchemes:    ?array
     *  documentation:      ?array
     *  /*
     * ]
     *
     * @return ApiDefinition
     */
    public static function createFromArray($title, array $data = [])
    {
        $apiDefinition = new static($title);

        if (isset($data['version'])) {
            $apiDefinition->version  = $data['version'];
        }

        if (isset($data['baseUrl'])) {
            $apiDefinition->baseUri = $data['baseUrl'];
        }

        // support for RAML 0.8
        if (isset($data['baseUri'])) {
            $apiDefinition->baseUri = $data['baseUri'];
        }

        if (isset($data['baseUriParameters'])) {
            foreach ($data['baseUriParameters'] as $key => $baseUriParameter) {
                $apiDefinition->addBaseUriParameter(
                    BaseUriParameter::createFromArray($key, $baseUriParameter)
                );
            }
        }

        if (isset($data['mediaType'])) {
            $apiDefinition->defaultMediaTypes = (array) $data['mediaType'];
        }

        if (isset($data['protocols'])) {
            foreach ($data['protocols'] as $protocol) {
                $apiDefinition->addProtocol($protocol);
            }
        }

        if (!$apiDefinition->protocols) {
            $apiDefinition->setProtocolsFromBaseUri();
        }

        if (isset($data['defaultMediaType'])) {
            $apiDefinition->setDefaultMediaType($data['defaultMediaType']);
        }

        if (isset($data['schemas']) && isset($data['types'])) {
            throw new MutuallyExclusiveElementsException();
        }

        if (isset($data['schemas'])) {
            foreach ($data['schemas'] as $name => $schema) {
                $apiDefinition->addType(ApiDefinition::determineType($name, $schema));
            }
        }

        if (isset($data['securitySchemes'])) {
            foreach ($data['securitySchemes'] as $name => $securityScheme) {
                $apiDefinition->addSecurityScheme(SecurityScheme::createFromArray($name, $securityScheme));
            }
        }

        if (isset($data['securedBy'])) {
            foreach ($data['securedBy'] as $securedBy) {
                if ($securedBy) {
                    $apiDefinition->addSecuredBy($apiDefinition->getSecurityScheme($securedBy));
                } else {
                    $apiDefinition->addSecuredBy(SecurityScheme::createFromArray('null', [], $apiDefinition));
                }
            }
        }

        if (isset($data['documentation'])) {
            foreach ($data['documentation'] as $title => $documentation) {
                $apiDefinition->addDocumentation($title, $documentation);
            }
        }

        if (isset($data['types'])) {
            foreach ($data['types'] as $name => $definition) {
                $apiDefinition->addType(ApiDefinition::determineType($name, $definition));
            }
        }

        // resolve type inheritance
        $apiDefinition->getTypes()->applyInheritance();

        // ---

        foreach ($data as $resourceName => $resource) {
            // check if actually a resource
            if (strpos($resourceName, '/') === 0) {
                $apiDefinition->addResource(
                    Resource::createFromArray(
                        $resourceName,
                        $resource,
                        $apiDefinition
                    )
                );
            }
        }

        return $apiDefinition;
    }

    // ---

    /**
     * Get a resource by a uri
     *
     * @param string $uri
     *
     * @throws InvalidKeyException
     *
     * @return \Raml\Resource
     */
    public function getResourceByUri($uri)
    {
        // get rid of everything after the ?
        $uri = strtok($uri, '?');

        $resources = $this->getResourcesAsArray($this->resources);
        foreach ($resources as $resource) {
            if ($resource->matchesUri($uri)) {
                return $resource;
            }
        }
        // we never returned so throw exception
        throw new ResourceNotFoundException($uri);
    }

    /**
     * Get a resource by a path
     *
     * @param string $path
     *
     * @throws InvalidKeyException
     *
     * @return \Raml\Resource
     */
    public function getResourceByPath($path)
    {
        // get rid of everything after the ?
        $path = strtok($path, '?');

        $resources = $this->getResourcesAsArray($this->resources);
        foreach ($resources as $resource) {
            if ($path === $resource->getUri()) {
                return $resource;
            }
        }
        // we never returned so throw exception
        throw new ResourceNotFoundException($path);
    }


    /**
     * Returns all the resources as a URI, essentially documenting the entire API Definition.
     * This will output, by default, an array that looks like:
     *
     * GET /songs => [/songs, GET, Raml\Method]
     * GET /songs/{songId} => [/songs/{songId}, GET, Raml\Method]
     *
     * @param RouteFormatterInterface $formatter
     *
     * @return RouteFormatterInterface
     */
    public function getResourcesAsUri(RouteFormatterInterface $formatter = null)
    {
        if (!$formatter) {
            $formatter = new NoRouteFormatter();
        }

        $formatter->format($this->getMethodsAsArray($this->resources));

        return $formatter;
    }

    /**
     * @param \Raml\Resource[] $resources
     *
     * @return \Raml\Resource[]
     */
    private function getResourcesAsArray($resources)
    {
        $resourceMap = [];

        // Loop over each resource to build out the full URI's that it has.
        foreach ($resources as $resource) {
            $resourceMap[$resource->getUri()] = $resource;

            $resourceMap = array_merge_recursive($resourceMap, $this->getResourcesAsArray($resource->getResources()));
        }

        return $resourceMap;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return ($this->version) ? str_replace('{version}', $this->version, $this->baseUri) : $this->baseUri;
    }

    public function setBaseUri($baseUrl)
    {
        $this->baseUri = $baseUrl;

        if (!$this->protocols) {
            $protocol = strtoupper(parse_url($this->baseUri, PHP_URL_SCHEME));
            if (!empty($protocol)) {
                $this->protocols = [$protocol];
            }
        }
    }

    /**
     * Get the base uri parameters
     *
     * @return NamedParameter[]
     */
    public function getBaseUriParameters()
    {
        return $this->baseUriParameters;
    }

    /**
     * Add a new base uri parameter
     *
     * @param NamedParameter $namedParameter
     */
    public function addBaseUriParameter(NamedParameter $namedParameter)
    {
        $this->baseUriParameters[$namedParameter->getKey()] = $namedParameter;
    }

    // --

    /**
     * @return boolean
     */
    public function supportsHttp()
    {
        return in_array(self::PROTOCOL_HTTP, $this->protocols, true);
    }

    /**
     * @return boolean
     */
    public function supportsHttps()
    {
        return in_array(self::PROTOCOL_HTTPS, $this->protocols, true);
    }

    /**
     * @return array
     */
    public function getProtocols()
    {
        return $this->protocols;
    }

    /**
     * @param string $protocol
     * @throws \InvalidArgumentException
     */
    private function addProtocol($protocol)
    {
        if (!in_array($protocol, [self::PROTOCOL_HTTP, self::PROTOCOL_HTTPS])) {
            throw new InvalidProtocolException(sprintf('"%s" is not a valid protocol', $protocol));
        }

        if (!in_array($protocol, $this->protocols, true)) {
            $this->protocols[] = $protocol;
        }
    }

    // ---

    /**
     * Get the default media type
     *
     * @return string[]
     */
    public function getDefaultMediaTypes()
    {
        return $this->defaultMediaTypes;
    }

    /**
     * Set a default media type
     *
     * @param $defaultMediaType
     */
    public function setDefaultMediaType($defaultMediaType)
    {
        // @todo - Should this validate?
        $this->defaultMediaType = $defaultMediaType;
    }

    // --

    /**
     * Get the schemas defined in the root of the API
     *
     * @return array[]
     */
    public function getSchemaCollections()
    {
        return $this->types;
    }

    /**
     * Add an schema
     *
     * @param string $collectionName
     * @param array  $schemas
     */
    public function addSchemaCollection($collectionName, $schemas)
    {
        $this->schemaCollections[$collectionName] = [];

        foreach ($schemas as $schemaName => $schema) {
            $this->addSchema($collectionName, $schemaName, $schema);
        }
    }

    /**
     * Add a new schema to a collection
     *
     * @param string                            $collectionName
     * @param string                            $schemaName
     * @param string|SchemaDefinitionInterface  $schema
     *
     * @throws InvalidSchemaDefinitionException
     */
    private function addSchema($collectionName, $schemaName, $schema)
    {
        if (!is_string($schema) && !$schema instanceof SchemaDefinitionInterface) {
            throw new InvalidSchemaDefinitionException();
        }

        $this->schemaCollections[$collectionName][$schemaName] = $schema;
    }

    // --

    /**
     * Get the documentation of the API
     *
     * @return array
     */
    public function getDocumentationList()
    {
        return $this->documentationList;
    }

    /**
     * Add a piece of documentation to the documentation list
     *
     * @param string $title
     * @param string $documentation
     */
    public function addDocumentation($title, $documentation)
    {
        $this->documentationList[$title] = $documentation;
    }

    /**
     * Determines the right Type and returns an instance
     *
     * @param string                    $name       Name of type.
     * @param array                     $definition Definition of type.
     * @param \Raml\TypeCollection|null $typeCollection Type collection object.
     *
     * @return \Raml\TypeInterface Returns a (best) matched type object.
     **/
    public static function determineType($name, $definition)
    {
        // check if we can find a more appropriate Type subclass
        $straightForwardTypes = [
            'time-only',
            'datetime',
            'datetime-only',
            'date-only',
            'number',
            'integer',
            'boolean',
            'string',
            'null',
            'nil',
            'file',
            'array',
            'object'
        ];
        if (is_string($definition)) {
            $definition = ['type' => $definition];
        } elseif (is_array($definition)) {
            if (!array_key_exists('type', $definition)) {
                $definition['type'] = isset($definition['properties']) ? 'object' : 'string';
            }
        } else {
            throw new \Exception('Invalid datatype for $definition parameter.');
        }

        $type = $definition['type'] ?: 'null';

        if (!in_array($type, ['','any'])) {
            if (in_array($type, $straightForwardTypes)) {
                $className = sprintf(
                    'Raml\Types\%sType',
                    StringTransformer::convertString($type, StringTransformer::UPPER_CAMEL_CASE)
                );
                return forward_static_call_array([$className,'createFromArray'], [$name, $definition]);
            }
            // if $type contains a '|' we can savely assume it's a combination of types (union)
            if (strpos($type, '|') !== false) {
                return UnionType::createFromArray($name, $definition);
            }
            // if $type contains a '[]' it means we have an array with a item restriction
            if (strpos($type, '[]') !== false) {
                return ArrayType::createFromArray($name, $definition);
            }
            // no standard type found so this must be a reference to a custom defined type
            // since the actual definition can be defined later then when it is referenced
            // we create a proxy object for lazy loading when it is needed
            return LazyProxyType::createFromArray($name, $definition);
        }

        // No subclass found, let's use base class
        return Type::createFromArray($name, $definition);
    }

    /**
     * Add data type
     *
     * @param \Raml\TypeInterface $type
     */
    public function addType(TypeInterface $type)
    {
        $this->types->add($type);
    }

    /**
     * Get data types
     *
     * @return \Raml\TypeCollection
     */
    public function getTypes()
    {
        return $this->types;
    }

    // --

    /**
     * Get the resources tree
     *
     * @return \Raml\Resource[]
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Add an additional resource
     *
     * @param \Raml\Resource $resource
     */
    public function addResource(Resource $resource)
    {
        $this->resources[$resource->getUri()] = $resource;
    }

    // --

    /**
     * Get a security scheme by it's name
     *
     * @param string $schemeName
     *
     * @return SecurityScheme
     */
    public function getSecurityScheme($schemeName)
    {
        return $this->securitySchemes[$schemeName];
    }

    /**
     * Add an additional security scheme
     *
     * @param SecurityScheme $securityScheme
     */
    public function addSecurityScheme(SecurityScheme $securityScheme)
    {
        $this->securitySchemes[$securityScheme->getKey()] = $securityScheme;
    }

    /**
     * Get a list of security schemes that the whole API is secured by
     *
     * @return SecurityScheme[]
     */
    public function getSecuredBy()
    {
        return $this->securedBy;
    }

    /**
     * Add an additional security scheme to the list of schemes the whole API is secured by
     *
     * @param SecurityScheme $securityScheme
     */
    public function addSecuredBy(SecurityScheme $securityScheme)
    {
        $this->securedBy[$securityScheme->getKey()] = $securityScheme;
    }

    // ---

    /**
     * Recursive function that generates a flat array of the entire API Definition
     *
     * GET /songs => [api.example.org, /songs, GET, [https], Raml\Method]
     * GET /songs/{songId} => [api.example.org, /songs/{songId}, GET, [https], Raml\Method]
     *
     * @param \Raml\Resource[] $resources
     *
     * @return array[BasicRoute]
     */
    private function getMethodsAsArray(array $resources)
    {
        $all = [];
        $baseUrl = $this->getBaseUri();
        $protocols = $this->protocols;

        // Loop over each resource to build out the full URI's that it has.
        foreach ($resources as $resource) {
            $path = $resource->getUri();

            foreach ($resource->getMethods() as $method) {
                $all[$method->getType() . ' ' . $path] = new BasicRoute(
                    $baseUrl,
                    $path,
                    $protocols,
                    $method->getType(),
                    $resource->getUriParameters(),
                    $resource->getMethod($method->getType())
                );
            }

            $all = array_merge_recursive($all, $this->getMethodsAsArray($resource->getResources()));
        }

        return $all;
    }

    private function setProtocolsFromBaseUri()
    {
        $schema = strtoupper(parse_url($this->baseUri, PHP_URL_SCHEME));

        if (empty($schema)) {
            $this->protocols = [self::PROTOCOL_HTTPS, self::PROTOCOL_HTTP];
        } else {
            $this->protocols = [$schema];
        }
    }
}
