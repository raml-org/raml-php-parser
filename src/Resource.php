<?php
namespace Raml;

class Resource
{
    /**
     * @var array
     */
    private static $knownMethods = ['get', 'post', 'put', 'delete', 'patch'];

    /**
     * @var array
     */
    private $subResources = [];

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string;
     */
    private $displayName;

    /**
     * @var array
     */
    private $baseUriParameters = [];

    /**
     * @var string
     */
    private $description;

    /**
     * Create a new Resource from an array
     *
     * @param $data
     */
    public function __construct($uri, $data, $baseUri)
    {
        $this->uri = $uri;

        if (isset($data['displayName'])) {
            $this->displayName = $data['displayName'];
        } else {
            $this->displayName = $this->convertUriToDisplayName($uri);
        }

        $this->baseUriParameters = (isset($data['baseUriParameters'])) ? $data['baseUriParameters'] : [];

        if ($data) {
            foreach ($data as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $this->subResources[$key] = new Resource($key, $value, $baseUri.$uri);
                } elseif (in_array($key, self::$knownMethods)) {
                    $this->methods[strtoupper($key)] = new Method($key, $value);
                }
            }
        }
    }

    /**
     * Returns the uri of the resource
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

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
     * Returns all the child resources of this resource
     *
     * @return array
     */
    public function getResources()
    {
        return $this->subResources;
    }

    /**
     * Returns an associative array of the methods that this resource supports
     * where the key is the method type, and the value is an instance of `\Raml\Method`
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Get a method by it's method name (get, post,...)
     *
     * @param string $method
     * @return \Raml\Method
     */
    public function getMethod($method)
    {
        $method = strtoupper($method);
        return isset($this->methods[$method]) ? $this->methods[$method] : null;
    }

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
     * If a display name is not provided then we attempt to construct a decent on from the uri.
     *
     * @param string $uri
     * @return string
     */
    private function convertUriToDisplayName($uri)
    {
        $separators = ['-', '_'];
        return ucwords(str_replace($separators, ' ', substr($uri, 1)));
    }
}
