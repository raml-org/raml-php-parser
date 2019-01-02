<?php

namespace Raml\SecurityScheme\SecuritySettings;

use Raml\SecurityScheme\SecuritySettingsInterface;

class DefaultSecuritySettings implements SecuritySettingsInterface, \ArrayAccess
{
    /**
     * Supports all types
     */
    const TYPE = '*';

    /**
     * The security settings
     *
     * @var array
     */
    private $settings = [];

    /**
     * Flesh out the settings
     *
     * @param SecuritySettingsInterface $sourceSettings
     *
     * @throws \Exception
     *
     * @return DefaultSecuritySettings
     */
    public static function createFromArray(array $data, SecuritySettingsInterface $sourceSettings = null)
    {
        if ($sourceSettings && !$sourceSettings instanceof self) {
            throw new \InvalidArgumentException('Provide an instance of DefaultSecuritySettings for $sourceSettings');
        }

        $settings = $sourceSettings ? clone $sourceSettings : new static();
        \assert($settings instanceof self);
        $settings->mergeSettings($data);

        return $settings;
    }

    /**
     * Merge new settings into the current settings
     *
     */
    public function mergeSettings(array $newSettings)
    {
        $this->settings = \array_replace($this->settings, $newSettings);
    }

    /**
     * Sets a settings value
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->settings[] = $value;
        } else {
            $this->settings[$offset] = $value;
        }
    }

    /**
     * Check if a settings value exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
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
     */
    public function offsetUnset($offset)
    {
        unset($this->settings[$offset]);
    }

    /**
     * Get a single settings value
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     *
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
