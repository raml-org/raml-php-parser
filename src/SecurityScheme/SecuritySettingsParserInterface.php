<?php
namespace Raml\SecurityScheme;

/**
 * Defines the interface for security settings.
 */
interface SecuritySettingsParserInterface
{
    /**
     * Create a security settings object from an array of data
     *
     * @param array $data
     *
     * @return object[]
     */
    public function createSecuritySettings(array $data = []);

    /**
     * Returns a list of the compatible types
     *
     * @return string[]
     */
    public function getCompatibleTypes();
}
