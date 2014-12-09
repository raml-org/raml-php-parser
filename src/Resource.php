<?php
namespace Raml;

class Resource
{
    /**
     * Valid METHODS
     * - Currently missing OPTIONS as this is unlikely to be specified in RAML
     * @var array
     */
    private static $knownMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

    // ---

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

    // ---

    /**
     * Create a new Resource from an array
     *
     * @param string $uri
     * @param array  $data
     * @param string $baseUri
     */
    public function __construct($uri, $data, $baseUri)
    {
        $this->uri = $uri;
        $this->displayName = $this->getArrayValue($data, 'displayName', $this->convertUriToDisplayName($uri));
        $this->description = $this->getArrayValue($data, 'description');
        $this->baseUriParameters = $this->getArrayValue($data, 'baseUriParameters', []);

        if ($data) {
            foreach ($data as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $this->subResources[$key] = new Resource($uri.$key, $value, $baseUri.$uri);
                } elseif (in_array(strtoupper($key), self::$knownMethods)) {
                    $this->methods[strtoupper($key)] = new Method($key, $value);
                }
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
     * @throws \Exception
     *
     * @return null
     */
    private function getArrayValue($data, $key, $defaultValue = null)
    {
        return isset($data[$key]) ? $data[$key] : $defaultValue;
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
        return $this->getArrayValue($this->methods, strtoupper($method));
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
     * If a display name is not provided then we attempt to construct a decent one from the uri.
     *
     * @param string $uri
     * @return string
     */
    private function convertUriToDisplayName($uri)
    {
        $separators = ['-', '_'];
        $uriParts = explode('/', $uri);
        return ucwords(str_replace($separators, ' ', end($uriParts)));
    }
}
