<?php
namespace Raml;

class Resource
{
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

    // ---

    private $baseUriParameters = [];

    // ---


    /**
     * Create a new Resource from an array
     *
     * @param $data
     */
    public function __construct($uri, $data, $baseUri)
    {
        $this->uri = $uri;

        $this->displayName = (isset($data['displayName'])) ? $data['displayName'] : $this->convertUriToDisplayName($uri);
        $this->baseUriParameters = (isset($data['baseUriParameters'])) ? $data['baseUriParameters'] : [];

        if($data) {
            foreach ($data as $key => $value) {
                if (strpos($key, '/') === 0) {
                    $this->subResources[$key] = new Resource($key, $value, $baseUri.$uri);
                } elseif(in_array($key, self::$knownMethods)) {
                    $this->methods[$key] = new Method($key, $value);
                }
            }
        }
    }

    /**
     * If a display name is not provided then we attempt to construct a decent on from the uri.
     * @param string $uri
     *
     * @return string
     */
    private function convertUriToDisplayName($uri)
    {
        $separators = ['-', '_'];
        return ucwords(str_replace($separators, ' ', substr($uri, 1)));
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

    /**
     * Returns the display name of the resource
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function getResources()
    {
        return $this->subResources;
    }

    /**
     * Get a method by it's method name (get, post,...)
     *
     * @param string $method
     *
     * @return \Raml\Method
     */
    public function getMethod($method)
    {
        return isset($this->methods[$method]) ? $this->methods[$method] : null;
    }


}