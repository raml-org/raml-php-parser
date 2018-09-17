<?php

namespace Raml;

use Raml\Exception\InvalidKeyException;

/**
 * @see http://raml.org/spec.html#web-forms
 */
class WebFormBody extends NamedParameter implements BodyInterface
{
    /**
     * @var NamedParameter[]
     */
    private $namedParameters = [];

    /**
     * List of valid media types
     *
     * @var string[]
     */
    public static $validMediaTypes = [
        'application/x-www-form-urlencoded',
        'multipart/form-data',
    ];

    /**
     * Create a new Query Parameter
     *
     * @param string $mediaType
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($mediaType)
    {
        if (!in_array($mediaType, self::$validMediaTypes, true)) {
            throw new \InvalidArgumentException(sprintf('%s is invalid type', $mediaType));
        }

        parent::__construct($mediaType);
    }

    /**
     * Get the media type
     *
     * @return string
     */
    public function getMediaType()
    {
        return $this->getKey();
    }

    /**
     * Create a new WebFormObject from an array
     *
     * @param string $key The valid media type to use as the key
     * @param array $data The array of data to create NamedParameter objects from
     * @return WebFormBody
     */
    public static function createFromArray($key, array $data = [])
    {
        $webFormBody = new static($key);

        if (isset($data['formParameters'])) {
            foreach ($data['formParameters'] as $namedParam => $namedParamData) {
                if (is_array($namedParamData)) {
                    $webFormBody->addParameter(NamedParameter::createFromArray($namedParam, $namedParamData));
                }
            }
        }

        return $webFormBody;
    }

    /**
     * Add a NamedParameter object to the list
     *
     * @param NamedParameter $namedParameter
     */
    public function addParameter(NamedParameter $namedParameter)
    {
        $this->namedParameters[$namedParameter->getKey()] = $namedParameter;
    }

    /**
     * Get a named parameter object by key name
     *
     * @param string $key The name of the key for the named parameter
     * @return NamedParameter
     *
     * @throws InvalidKeyException
     */
    public function getParameter($key)
    {
        if (empty($this->namedParameters[$key])) {
            throw new InvalidKeyException($key);
        }

        return $this->namedParameters[$key];
    }

    /**
     * Get all NamedParameter objects
     *
     * @return NamedParameter[]
     */
    public function getParameters()
    {
        return $this->namedParameters;
    }
}
