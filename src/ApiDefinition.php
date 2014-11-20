<?php
namespace Raml;

use Raml\Formatters\RouteFormatterInterface;

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
     * Create a new API Definition from an array
     *
     * @param $data
     */
    public function __construct($data)
    {
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
     * @return array
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * @return string
     */
    public function getDefaultMediaType()
    {
        return $this->defaultMediaType;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @return boolean
     */
    public function supportsHttp()
    {
        return in_array(ApiDefinition::HTTP, $this->protocols);
    }

    /**
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
     * Get a resource by a uri
     *
     * @return \Raml\Resource
     */
    public function getResourceByUri($uri)
    {
        $uriParts = explode('/', preg_replace('/[\.|\?].*/', '', $uri));
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
                if ('/'.$part === $potentialResource->getUri() || strpos($potentialResource->getUri(), '/{') === 0) {
                    if ($part === $uriParts[count($uriParts)-1]) {
                        $resource = $potentialResource;
                    }
                    $resources = $potentialResource->getResources();
                }
            }
        }

        return $resource;
    }

    /**
     * Returns all the resources as a URI, essentially documentating
     * the entire API Definition.
     *
     * @param RouteFormatterInterface $formatter
     * @return array
     */
    public function getResourcesAsUri(RouteFormatterInterface $formatter, $resources, $baseUri = '')
    {
        $all = [];

        // Loop over each resource to build out the full URI's that it has.
        foreach ($resources as $resource) {
            $path = $baseUri . $resource->getUri();

            foreach ($resource->getMethods() as $method) {
                $all[$path] = [
                    'method' => $method->getType(),
                    'response' => $resource->getMethod($method->getType())
                ];
            }

            $all = array_merge_recursive($all, $this->getResourcesAsUri($formatter, $resource->getResources(), $path));
        }

        return $formatter->format($all);
    }
}
