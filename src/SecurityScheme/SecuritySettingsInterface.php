<?php
namespace Raml\SecurityScheme;

/**
 * Defines the interface for security settings.
 */
interface SecuritySettingsInterface
{
    /**
     * Create a security settings object from an array of data
     *
     * @param array                     $data
     * @param SecuritySettingsInterface $sourceSettings
     *
     * @return SecuritySettingsInterface
     */
    public static function createFromArray(array $data, SecuritySettingsInterface $sourceSettings = null);
}
