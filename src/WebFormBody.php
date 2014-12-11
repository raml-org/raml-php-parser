<?php

namespace Raml;

/**
 * @see http://raml.org/spec.html#web-forms
 */
class WebFormBody extends NamedParameter implements BodyInterface, ArrayInstantiationInterface
{
    /**
     * List of valid media types
     *
     * @var array
     */
    public static $validMediaTypes = [
        'application/x-www-form-urlencoded',
        'multipart/form-data'
    ];

    /**
     * Create a new Query Parameter
     *
     * @param string $mediaType
     *
     * @throws \Exception
     */
    public function __construct($mediaType)
    {
        if (!in_array($mediaType, self::$validMediaTypes)) {
            throw new \Exception('Invalid type');
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
}
