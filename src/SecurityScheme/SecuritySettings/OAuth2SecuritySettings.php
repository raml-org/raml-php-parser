<?php

namespace Raml\SecurityScheme\SecuritySettings;

class OAuth2SecuritySettings
{
    const TYPE = 'OAuth 2.0';

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
        $defaults = array_fill_keys(array('authorizationUri', 'accessTokenUri', 'authorizationGrants', 'scopes'), null);
        $this->settings = array_replace($defaults, $settings);
    }

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
     * Get the Access Token URI
     *
     * @return string
     */
    public function getAccessTokenUri()
    {
        return $this->settings['accessTokenUri'];
    }

    /**
     * Set the Access Token URI
     *
     * @param string $accessTokenUri
     */
    public function setAccessTokenUri($accessTokenUri)
    {
        $this->settings['accessTokenUri'] = $accessTokenUri;
    }

    // --

    /**
     * Get the list of Authorization Grants
     *
     * @return string[]
     */
    public function getAuthorizationGrants()
    {
        return $this->settings['authorizationGrants'];
    }

    /**
     * Add an additional Authorization Grant
     *
     * @param string $authorizationGrant
     */
    public function addAuthorizationGrants($authorizationGrant)
    {
        $this->settings['authorizationGrants'][] = $authorizationGrant;
    }

    // --

    /**
     * Set the list of scopes
     *
     * @return string[]
     */
    public function getScopes()
    {
        return $this->settings['scopes'];
    }

    /**
     * Add a additional scope
     *
     * @param string $scope
     */
    public function addScope($scope)
    {
        $this->settings['scopes'][] = $scope;
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
