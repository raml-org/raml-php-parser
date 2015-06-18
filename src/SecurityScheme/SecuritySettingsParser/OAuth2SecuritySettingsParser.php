<?php

namespace Raml\SecurityScheme\SecuritySettingsParser;

use Raml\SecurityScheme\SecuritySettings\OAuth2SecuritySettings;
use Raml\SecurityScheme\SecuritySettingsParserInterface;

class OAuth2SecuritySettingsParser implements SecuritySettingsParserInterface
{

    // ---
    // SecuritySettingsParserInterface

    /**
     * Create a new OAuth2 Security Settings Object from array data
     *
     * @param array $data
     * [
     *  authorizationUri:       ?string
     *  accessTokenUri:         ?string
     *  authorizationGrants:    ?string[]
     *  scopes:                 ?string[]
     * ]
     *
     * @return OAuth2SecuritySettings
     */
    public function createSecuritySettings(array $data = [])
    {
        $securitySetting = new OAuth2SecuritySettings();

        $securitySetting->createFromArray($data);

        return $securitySetting;
    }

    /**
     * Get a list of supported types
     *
     * @return array
     */
    public function getCompatibleTypes()
    {
        return [OAuth2SecuritySettings::TYPE];
    }
}
