<?php
namespace Raml;

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

    // ---

    /**
     * Create a new API Definition from an array
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (!isset($data['title'])) {
            throw new \Exception('No Title supplied in RAML');
        }

        $this->title = $data['title'];
        $this->version = isset($data['version']) ? $data['version'] : null;

        $this->baseUri = isset($data['baseUri']) ? $data['baseUri'] : null;
        $this->uriParameters = isset($data['uriParameters']) ? $data['uriParameters'] : null;

        $this->defaultMediaType = isset($data['defaultMediaType']) ? $data['defaultMediaType'] : null;
        $this->documentation = isset($data['documentation']) ? $data['documentation'] : null;

        if (!isset($data['protocols']) && isset($data['baseUri'])) {
            $this->protocols = [parse_url($data['baseUri'], PHP_URL_SCHEME)];
        } else {
            $this->protocols = isset($data['protocols']) ? $data['protocols'] : null;
        }

        foreach ($data as $resourceName => $resource) {
            // check if actually a resource
            if (strpos($resourceName, '/') === 0) {
                $this->resources[$resourceName] = new Resource($resourceName, $resource, $this->baseUri);
            }
        }
    }

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
        return $this->baseUri;
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

    // ---

    /**
     * Get a resource by a uri
     *
     * @param string $testUri
     *
     * @return \Raml\Resource
     */
    public function getResourceByUri($testUri)
    {
        // @todo - optimise this method - must be a better way of doing it.

        $uriParts = explode('/', preg_replace('/[\.|\?].*/', '', $testUri));
        $resources = $this->getResources();
        $resource = null;

        foreach ($uriParts as $part) {
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
                    if ($part === $uriParts[count($uriParts)-1]) {
                        $resource = $potentialResource;
                    }
                    $resources = $potentialResource->getResources();
                }
            }
        }

        if ($resource) {
            return $resource;
        }


        throw new \Exception('Resource not found for uri "'.$testUri.'"');
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
     * Recursive function that generates a flat array of the  entire API Definition
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
