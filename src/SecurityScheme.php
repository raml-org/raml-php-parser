<?php

namespace Raml;

use Raml\SecurityScheme\SecuritySchemeDescribedBy;

/**
 * Security scheme
 *
 * @see http://raml.org/spec.html#declaration
 */
class SecurityScheme implements ArrayInstantiationInterface
{

    /**
     * The key of the security scheme
     *
     * @var string
     */
    private $key;

    // --

    /**
     * The description of the security schema
     *
     * @see http://raml.org/spec.html#description
     *
     * @var string
     */
    private $description;

    /**
     * The type of the security schema (optional)
     *
     * @see http://raml.org/spec.html#type
     *
     * @var string
     */
    private $type;

    /**
     * The structure of the request
     *
     * @see http://raml.org/spec.html#describedby
     *
     * @var SecuritySchemeDescribedBy
     */
    private $describedBy;

    /**
     * The security settings
     *
     * @see http://raml.org/spec.html#settings
     *
     * @var object
     */
    private $settings;

    // ---

    /**
     * Create a new security scheme
     *
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Create a security scheme from an array
     *
     * @param string $key
     * @param array $data
     * [
     *  description: ?string
     *  type:        ?string
     *  describedBy: ?string[]
     *  settings:    ?object[]
     * ]
     * @param ApiDefinition $apiDefinition
     *
     * @return SecurityScheme
     */
    public static function createFromArray($key, array $data = [], ApiDefinition $apiDefinition = null)
    {
        $securityScheme = new static($key);

        if (isset($data['description'])) {
            $securityScheme->setDescription($data['description']);
        }

        if (isset($data['type'])) {
            $securityScheme->setType($data['type']);
        }

        if (isset($data['describedBy'])) {
            $securityScheme->setDescribedBy(
                SecuritySchemeDescribedBy::createFromArray('describedBy', $data['describedBy'])
            );
        }

        if (isset($data['settings'])) {
            $securityScheme->setSettings($data['settings']);
        }


        return $securityScheme;
    }

    /**
     * Get the key of the security setting
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    // ---

    /**
     * Get the description of the security scheme
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description of the security scheme
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // --

    /**
     * Get the type of the security scheme
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of the security scheme
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    // --

    /**
     * Get the implementation details of the security scheme
     *
     * @return SecuritySchemeDescribedBy
     */
    public function getDescribedBy()
    {
        return $this->describedBy;
    }

    /**
     * Set the implementation details of the security scheme
     *
     * @param SecuritySchemeDescribedBy $describedBy
     */
    public function setDescribedBy(SecuritySchemeDescribedBy $describedBy)
    {
        $this->describedBy = $describedBy;
    }

    // ---

    /**
     * Get the settings of the security scheme
     *
     * @return object
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set the settings for the security scheme
     *
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}
