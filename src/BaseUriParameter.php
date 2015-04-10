<?php
namespace Raml;

/**
 * Base Uri Parameters
 *
 * @see http://raml.org/spec.html#base-uri-parameters
 */
class BaseUriParameter extends NamedParameter
{

    /**
     * Valid types
     *
     * @var array
     */
    protected $validTypes = [
        self::TYPE_STRING,
        self::TYPE_NUMBER,
        self::TYPE_INTEGER
    ];

    // ---

    /**
     * If the parameter is required (default: true)
     *
     * @see http://raml.org/spec.html#required
     *
     * @var boolean
     */
    protected $required = true;
}
