<?php

namespace Raml\SecurityScheme\SecuritySettings;

class OAuth1SecuritySettings
{
    const TYPE = 'OAuth 1.0';

    // --

    /**
     * The array of settings data
     * @var array
     */
    private $settings;

    // ---
    
    /**
     * Flesh out the settings
     *
     * @param array $settings
     */
    public function createFromArray(array $settings)
    {
        $defaults = array_fill_keys(array('tokenCredentialsUri', 'requestTokenUri', 'authorizationUri'), null);
        $this->settings = array_replace($defaults, $settings);
    }

    /**
     * Get the Token Credentials URI
     *
     * @return string
     */
    public function getTokenCredentialsUri()
    {
        return $this->settings['tokenCredentialsUri'];
    }

    /**
     * Set the Token Credentials URI
     *
     * @param string $tokenCredentialsUri
     */
    public function setTokenCredentialsUri($tokenCredentialsUri)
    {
        $this->settings['tokenCredentialsUri'] = $tokenCredentialsUri;
    }

    // --

    /**
     * Get the Request Token URI
     *
     * @return string
     */
    public function getRequestTokenUri()
    {
        return $this->settings['requestTokenUri'];
    }

    /**
     * Set the Request Token URI
     *
     * @param string $requestTokenUri
     */
    public function setRequestTokenUri($requestTokenUri)
    {
        $this->settings['requestTokenUri'] = $requestTokenUri;
    }

    // --

    /**
     * Get the Authorization URI
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        return $this->settings['authorizationUri'];
    }

    /**
     * Set the Authorization URI
     *
     * @param string $authorizationUri
     */
    public function setAuthorizationUri($authorizationUri)
    {
        $this->settings['authorizationUri'] = $authorizationUri;
    }
    
    // --
    
    /**
     * Get the array of configured settings
     *
     * @return array The array of settings data
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
