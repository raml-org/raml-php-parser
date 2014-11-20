<?php
namespace Raml;

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

    // ---
    // Resources

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

    // ---

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

    // ---

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

    // ---

    public function supportsHTTP()
    {

    }

    public function supportsHTTPs()
    {

    }

    // ---

    /**
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }
}
