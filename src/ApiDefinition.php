<?php
namespace Raml;

use Raml\Exception\InvalidKeyException;
use Raml\Exception\ResourceNotFoundException;
use Raml\Formatters\RouteFormatterInterface;
use Raml\Formatters\NoRouteFormatter;

/**
 * The API Definition
 *
 * @package Raml
 */
class ApiDefinition
{
    const HTTP = 'http';
    const HTTPS = 'https';

    /**
     * The API Title (required)
     * {title}
     *
     * @var string
     */
    private $title;

    /**
     * The API Version (optional)
     * {version}
     *
     * @var string
     */
    private $version;

    /**
     * The Base URI (optional)
     * {baseUri}
     *
     * @var string
     */
    private $baseUri;

    /**
     * The Uri Parameters (optional)
     * {uriParameters}
     *
     * @var array
     */
    private $uriParameters;

    /**
     * The supported protocols (required)
     * {protocols}
     *
     * @var array
     */
    private $protocols;

    /**
     * The default media type (optional)
     * {defaultMediaType}
     *
     * @var string
     */
    private $defaultMediaType;

    /**
     * The documentation for the API (optional)
     * {documentation}
     *
     * @var array
     */
    private $documentation;

    /**
     * The resources the API supplies
     * {/*}
     *
     * @var array
     */
    private $resources;

    /**
     * The schemas the API supplies defined in the root
     * {/*}
     *
     * @var array
     */
    private $schemas;

    // ---

    /**
     * Create a new API Definition from an array
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->title = $this->getArrayValue($data, 'title', true);
        $this->version = $this->getArrayValue($data, 'version');

        $this->baseUri = $this->getArrayValue($data, 'baseUri');
        $this->uriParameters = $this->getArrayValue($data, 'uriParameters');

        $this->defaultMediaType = $this->getArrayValue($data, 'mediaType');
        // Keep BC
        if (!$this->defaultMediaType) {
            $this->defaultMediaType = $this->getArrayValue($data, 'defaultMediaType');
        }
        $this->documentation = $this->getArrayValue($data, 'documentation');

        $this->protocols =$this->getArrayValue($data, 'protocols');

        if (!$this->protocols && $this->baseUri) {
            $this->protocols = [parse_url($this->baseUri, PHP_URL_SCHEME)];
        }

        $this->schemas =$this->getArrayValue($data, 'schemas');

        foreach ($data as $resourceName => $resource) {
            // check if actually a resource
            if (strpos($resourceName, '/') === 0) {
                $this->resources[$resourceName] = new Resource($resourceName, $resource, $this->baseUri);
            }
        }
    }

    /**
     * Helper method to extract items from array
     *
     * @param array   $data
     * @param string  $key
     * @param boolean $required
     *
     * @throws InvalidKeyException
     *
     * @return null
     */
    private function getArrayValue($data, $key, $required = false)
    {
        if ($required && !isset($data[$key])) {
            throw new InvalidKeyException($key);
        }

        return isset($data[$key]) ? $data[$key] : null;
    }

    // ---

    /**
     * Get the title of the API
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the version string of the API
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the documentation of the API
     *
     * @return array
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Get the default media type
     *
     * @return string
     */
    public function getDefaultMediaType()
    {
        return $this->defaultMediaType;
    }

    /**
     * Get the base URI
     *
     * @return string
     */
    public function getBaseUri()
    {
        return str_replace('{version}', $this->version, $this->baseUri);
    }

    /**
     * Does the API support HTTP (non SSL) requests?
     *
     * @return boolean
     */
    public function supportsHttp()
    {
        return in_array(ApiDefinition::HTTP, $this->protocols);
    }

    /**
     * Does the API support HTTPS (SSL enabled) requests?
     *
     * @return boolean
     */
    public function supportsHttps()
    {
        return in_array(ApiDefinition::HTTPS, $this->protocols);
    }

    /**
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return array
     */
    public function getSchemas()
    {
        return $this->schemas;
    }

    // ---

    /**
     * Get a resource by a uri
     *
     * @param string $testUri
     * @throws ResourceNotFoundException
     * @return \Raml\Resource
     */
    public function getResourceByUri($testUri)
    {
        // @todo - optimise this method - must be a better way of doing it.

        $uriParts = explode('/', preg_replace('/[\.|\?].*/', '', $testUri));
        $resources = $this->getResources();
        $resource = null;

        $count = 0;

        foreach ($uriParts as $part) {
            ++$count;

            // if part is empty
            // exclude empty from beginning of string
            // or from //
            if (!$part) {
                continue;
            }

            foreach ($resources as $potentialResource) {
                $resourceUriParts = explode('/', $potentialResource->getUri());
                $uri = '/'.end($resourceUriParts);

                if ('/'.$part === $uri || strpos($uri, '/{') === 0) {
                    if ($count === count($uriParts)) {
                        $resource = $potentialResource;
                    }
                    $resources = $potentialResource->getResources();
                }
            }
        }

        if ($resource) {
            return $resource;
        }


        throw new ResourceNotFoundException($testUri);
    }

    /**
     * Returns all the resources as a URI, essentially documenting the entire API Definition.
     * This will output, by default, an array that looks like:
     *
     * GET /songs => [/songs, GET, Raml\Method]
     * GET /songs/{songId} => [/songs/{songId}, GET, Raml\Method]
     *
     * @param \Raml\Formatters\RouteFormatterInterface $formatter
     *
     * @return \Raml\Formatters\RouteFormatterInterface
     */
    public function getResourcesAsUri(RouteFormatterInterface $formatter = null)
    {
        if (!$formatter) {
            $formatter = new NoRouteFormatter();
        }

        $formatter->format($this->getResourcesAsArray($this->resources));

        return $formatter;
    }

    // ---

    /**
     * Recursive function that generates a flat array of the entire API Definition
     *
     * GET /songs => [/songs, GET, Raml\Method]
     * GET /songs/{songId} => [/songs/{songId}, GET, Raml\Method]
     *
     * @param array $resources
     * @param string $path
     *
     * @return array
     */
    private function getResourcesAsArray(array $resources)
    {
        $all = [];

        // Loop over each resource to build out the full URI's that it has.
        foreach ($resources as $resource) {
            $path = $resource->getUri();

            foreach ($resource->getMethods() as $method) {
                $all[$method->getType() . ' ' . $path] = [
                    'path' => $path,
                    'method' => $method->getType(),
                    'response' => $resource->getMethod($method->getType())
                ];
            }

            $all = array_merge_recursive($all, $this->getResourcesAsArray($resource->getResources()));
        }

        return $all;
    }
}
