<?php

namespace Raml;

/**
 * @see http://raml.org/spec.html#resources-and-nested-resources
 */
class Resource implements ArrayInstantiationInterface
{
    /**
     * The URI of this resource (required)
     * - Must begin with a "/"
     *
     * @see http://raml.org/spec.html#resources-and-nested-resources
     *
     * @var string
     */
    private $uri;

    /**
     * The display name (optional)
     * - defaults to URI
     *
     * @see http://raml.org/spec.html#display-name
     *
     * @var string;
     */
    private $displayName;

    /**
     * The description of the resource (optional)
     *
     * @see http://raml.org/spec.html#description
     *
     * @var string
     */
    private $description;

    /**
     * Override for the Base Uri Parameters
     *
     * @see http://raml.org/spec.html#base-uri-parameters
     *
     * @var NamedParameter[]
     */
    private $baseUriParameters = [];

    /**
     * List of uri parameters
     *
     * @var NamedParameter[]
     */
    private $uriParameters = [];

    /**
     * A list of security schemes
     *
     * @var SecurityScheme[]
     */
    private $securitySchemes = [];

    /**
     * List of resources under this resource
     *
     * @var self[]
     */
    private $subResources = [];

    /**
     * List of methods on this resource
     *
     * @var Method[]
     */
    private $methods = [];

    /**
     * @var TraitDefinition[]
     */
    private $traits = [];

    /**
     * @var Resource|null
     */
    private $parentResource;

    /**
     * @param string $uri
     * @param ApiDefinition $apiDefinition
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($uri, ApiDefinition $apiDefinition)
    {
        if (mb_strpos($uri, '/') !== 0) {
            throw new \InvalidArgumentException('URI must begin with a /');
        }

        $this->uri = $uri;

        foreach ($apiDefinition->getBaseUriParameters() as $baseUriParameter) {
            $this->addBaseUriParameter($baseUriParameter);
        }
    }

    /**
     * Create a Resource from an array
     *
     * @param string $uri
     * @param ApiDefinition $apiDefinition
     * @param array $data
     * [
     *  uri: string
     *  displayName: ?string
     *  description: ?string
     *  baseUriParameters: ?array
     * ]
     * @return self
     */
    public static function createFromArray($uri, array $data = [], ApiDefinition $apiDefinition = null)
    {
        $resource = new static($uri, $apiDefinition);

        if (isset($data['displayName'])) {
            $resource->setDisplayName($data['displayName']);
        } else {
            $resource->setDisplayName($uri);
        }

        if (isset($data['description'])) {
            $resource->setDescription($data['description']);
        }

        if (isset($data['baseUriParameters'])) {
            foreach ($data['baseUriParameters'] as $key => $baseUriParameter) {
                $resource->addBaseUriParameter(
                    BaseUriParameter::createFromArray($key, $baseUriParameter)
                );
            }
        }

        if (isset($data['uriParameters'])) {
            foreach ($data['uriParameters'] as $key => $uriParameter) {
                $resource->addUriParameter(
                    NamedParameter::createFromArray($key, $uriParameter ?: [])
                );
            }
        }

        if (isset($data['securedBy'])) {
            foreach ($data['securedBy'] as $key => $securedBy) {
                if ($securedBy) {
                    if (is_array($securedBy)) {
                        $key = array_keys($securedBy)[0];
                        $securityScheme = clone $apiDefinition->getSecurityScheme($key);
                        $securityScheme->mergeSettings($securedBy[$key]);
                        $resource->addSecurityScheme($securityScheme);
                    } else {
                        $resource->addSecurityScheme($apiDefinition->getSecurityScheme($securedBy));
                    }
                } else {
                    $resource->addSecurityScheme(SecurityScheme::createFromArray('null', [], $apiDefinition));
                }
            }
        }

        if (isset($data['is'])) {
            foreach ((array) $data['is'] as $traitName) {
                $resource->addTrait(TraitCollection::getInstance()->getTraitByName($traitName));
            }
        }

        foreach ($data as $key => $value) {
            if (strpos($key, '/') === 0) {
                $value = $value ?: [];
                if (isset($data['uriParameters'])) {
                    $currentParameters = isset($value['uriParameters']) ? $value['uriParameters'] : [];
                    $value['uriParameters'] = array_merge($currentParameters, $data['uriParameters']);
                }
                $resource->addResource(
                    self::createFromArray(
                        $uri . $key,
                        $value,
                        $apiDefinition
                    )
                );
            } elseif (in_array(strtoupper($key), Method::$validMethods, true)) {
                $resource->addMethod(
                    Method::createFromArray(
                        $key,
                        $value,
                        $apiDefinition
                    )
                );
            }
        }

        foreach ($resource->getMethods() as $method) {
            foreach ($resource->getTraits() as $trait) {
                $method->addTrait($trait);
            }
        }

        return $resource;
    }

    /**
     * Does a uri match this resource
     *
     * @param string $uri
     *
     * @return bool
     */
    public function matchesUri($uri)
    {
        $regexUri = $this->uri;

        foreach ($this->getUriParameters() as $uriParameter) {
            $matchPattern = $uriParameter->getMatchPattern();
            if ('^' === $matchPattern[0]) {
                $matchPattern = substr($matchPattern, 1);
            }

            if ('$' === substr($matchPattern, -1)) {
                $matchPattern = substr($matchPattern, 0, -1);
            }

            $regexUri = str_replace(
                '/{' . $uriParameter->getKey() . '}',
                '/' . $matchPattern,
                $regexUri
            );

            $regexUri = str_replace(
                '/~{' . $uriParameter->getKey() . '}',
                '/((' . $matchPattern . ')|())',
                $regexUri
            );
        }


        $regexUri = preg_replace('/\/{.*}/U', '\/([^/]+)', $regexUri);
        $regexUri = preg_replace('/\/~{.*}/U', '\/([^/]*)', $regexUri);
        // начало и конец регулярки - символ, который гарантированно не встретится
        $regexUri = chr(128) . '^' . $regexUri . '$' . chr(128);

        return (bool) preg_match($regexUri, $uri);
    }

    // ---

    /**
     * Returns the uri of the resource
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    // --

    /**
     * Returns the display name of the resource
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set the display name
     *
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    // --

    /**
     * Gets description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // --

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
     * Get the uri parameters
     *
     * @return NamedParameter[]
     */
    public function getUriParameters()
    {
        return $this->uriParameters;
    }

    /**
     * Add a new uri parameter
     *
     * @param NamedParameter $namedParameter
     */
    public function addUriParameter(NamedParameter $namedParameter)
    {
        $this->uriParameters[$namedParameter->getKey()] = $namedParameter;
    }

    // --

    /**
     * Returns all the child resources of this resource
     *
     * @return array
     */
    public function getResources()
    {
        return $this->subResources;
    }

    /**
     * Add a resource
     *
     * @param self $resource
     */
    public function addResource(self $resource)
    {
        $this->subResources[$resource->getUri()] = $resource;
        $resource->setParentResource($this);
    }

    // --

    /**
     * Add a method
     *
     * @param Method $method
     */
    public function addMethod(Method $method)
    {
        $this->methods[$method->getType()] = $method;

        foreach ($this->getSecuritySchemes() as $securityScheme) {
            $method->addSecurityScheme($securityScheme);
        }
    }

    /**
     * Returns an associative array of the methods that this resource supports
     * where the key is the method type, and the value is an instance of `\Raml\Method`
     *
     * @return \Raml\Method[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Get a method by it's method name (get, post,...)
     *
     * @param string $method
     *
     * @throws \Exception
     *
     * @return Method
     */
    public function getMethod($method)
    {
        $method = strtoupper($method);

        if (!isset($this->methods[$method])) {
            throw new \Exception('Method not found');
        }

        return $this->methods[$method];
    }

    /**
     * Get the list of security schemes
     *
     * @return SecurityScheme[]
     */
    public function getSecuritySchemes()
    {
        return $this->securitySchemes;
    }

    /**
     * @param SecurityScheme $securityScheme
     */
    public function addSecurityScheme(SecurityScheme $securityScheme)
    {
        $this->securitySchemes[$securityScheme->getKey()] = $securityScheme;
    }

    /**
     * @return TraitDefinition[]
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @param TraitDefinition $trait
     * @return $this
     */
    public function addTrait($trait)
    {
        $this->traits[] = $trait;

        return $this;
    }

    /**
     * @return Resource|null
     */
    public function getParentResource()
    {
        return $this->parentResource;
    }

    /**
     * @param Resource $parentResource
     *
     * @return $this
     */
    public function setParentResource($parentResource)
    {
        $this->parentResource = $parentResource;

        return $this;
    }
}
