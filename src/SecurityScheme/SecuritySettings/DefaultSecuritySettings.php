<?php

namespace Raml\SecurityScheme\SecuritySettings;

use Raml\SecurityScheme\SecuritySettingsInterface;

class DefaultSecuritySettings implements SecuritySettingsInterface, \ArrayAccess
{
    /**
     * Supports all types
     */
    const TYPE = '*';

    // --

    /**
     * The security settings
     *
     * @var array
     */
    private $settings = [];

    // ---
    // SecuritySettingsInterface

    /**
     * Flesh out the settings
     *
     * @param array                     $data
     * @param SecuritySettingsInterface $sourceSettings
     *
     * @throws \Exception
     *
     * @return DefaultSecuritySettings
     */
    public static function createFromArray(array $data, SecuritySettingsInterface $sourceSettings = null)
    {
        if ($sourceSettings && !$sourceSettings instanceof DefaultSecuritySettings) {
            throw new \Exception();
        }

        $settings = $sourceSettings ? clone $sourceSettings : new static();

        $settings->mergeSettings($data);

        return $settings;
    }

    // ---
    // \ArrayAccess

    /**
     * Merge new settings into the current settings
     *
     * @param $newSettings
     */
    public function mergeSettings($newSettings)
    {
        $this->settings = array_replace($this->settings, $newSettings);
    }

    // ---

    /**
     * Sets a settings value
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->settings[] = $value;
        } else {
            $this->settings[$offset] = $value;
        }
    }

    /**
     * Check if a settings value exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->settings[$offset]);
    }

    /**
     * Delete a settings value
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->settings[$offset]);
    }

    /**
     * Get a single settings value
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->settings[$offset]) ? $this->settings[$offset] : null;
    }
    
    /**
     * Get the array of settings data
     *
     * @return array
     */
    public function asArray()
    {
        return $this->settings;
    }
}
