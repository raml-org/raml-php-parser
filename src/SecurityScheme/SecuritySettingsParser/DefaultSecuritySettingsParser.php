<?php

namespace Raml\SecurityScheme\SecuritySettingsParser;

use Raml\SecurityScheme\SecuritySettings\DefaultSecuritySettings;
use Raml\SecurityScheme\SecuritySettingsParserInterface;

class DefaultSecuritySettingsParser implements SecuritySettingsParserInterface
{

    // ---
    // SecuritySettingsParserInterface

    /**
     * Create a new Default Security Settings Object from array data
     *
     * @param array $data
     *
     * @return DefaultSecuritySettings
     */
    public function createSecuritySettings(array $data = [])
    {
        return DefaultSecuritySettings::createFromArray($data);
    }

    /**
     * Get a list of supported types
     *
     * @return array
     */
    public function getCompatibleTypes()
    {
        return [DefaultSecuritySettings::TYPE];
    }
}
