<?php

namespace Raml\SecurityScheme\SecuritySettingsParser;

use Raml\SecurityScheme\SecuritySettings\OAuth1SecuritySettings;
use Raml\SecurityScheme\SecuritySettingsParserInterface;

class OAuth1SecuritySettingsParser implements SecuritySettingsParserInterface
{
    // ---
    // SecuritySettingsParserInterface

    /**
     * Create a new OAuth1 Security Settings Object from array data
     *
     * @param array $data
     * [
     *  requestTokenUri:     ?string
     *  authorizationUri:    ?string
     *  tokenCredentialsUri: ?string
     * ]
     *
     * @return OAuth1SecuritySettings
     */
    public function createSecuritySettings(array $data = [])
    {
        $securitySetting = new OAuth1SecuritySettings();

        if (isset($data['requestTokenUri'])) {
            $securitySetting->setRequestTokenUri($data['requestTokenUri']);
        }

        if (isset($data['authorizationUri'])) {
            $securitySetting->setAuthorizationUri($data['authorizationUri']);
        }

        if (isset($data['tokenCredentialsUri'])) {
            $securitySetting->setTokenCredentialsUri($data['tokenCredentialsUri']);
        }

        return $securitySetting;
    }

    /**
     * Get a list of supported types
     *
     * @return array
     */
    public function getCompatibleTypes()
    {
        return [OAuth1SecuritySettings::TYPE];
    }
}
