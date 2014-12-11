<?php

namespace Raml\SecurityScheme\SecuritySettings;

class OAuth2SecuritySettings
{
    const TYPE = 'OAuth 2.0';

    // --

    /**
     * The URI of the Authorization Endpoint as defined in RFC6749 [RFC6748] Section 3.1
     *
     * @var string
     */
    private $authorizationUri;

    /**
     * The URI of the Token Endpoint as defined in RFC6749 [RFC6748] Section 3.2
     *
     * @var string
     */
    private $accessTokenUri;

    /**
     * A list of the Authorization grants supported by the API As defined in RFC6749 [RFC6749]
     * Sections 4.1, 4.2, 4.3 and 4.4, can be any of: code, token, owner or credentials.
     *
     * @var string[]
     */
    private $authorizationGrants;

    /**
     * A list of scopes supported by the API as defined in RFC6749 [RFC6749] Section 3.3
     *
     * @var string[]
     */
    private $scopes;

    // ---

    /**
     * Get the Authorization URI
     *
     * @return string
     */
    public function getAuthorizationUri()
    {
        return $this->authorizationUri;
    }

    /**
     * Set the Authorization URI
     *
     * @param string $authorizationUri
     */
    public function setAuthorizationUri($authorizationUri)
    {
        $this->authorizationUri = $authorizationUri;
    }

    // --

    /**
     * Get the Access Token URI
     *
     * @return string
     */
    public function getAccessTokenUri()
    {
        return $this->accessTokenUri;
    }

    /**
     * Set the Access Token URI
     *
     * @param string $accessTokenUri
     */
    public function setAccessTokenUri($accessTokenUri)
    {
        $this->accessTokenUri = $accessTokenUri;
    }

    // --

    /**
     * Get the list of Authorization Grants
     *
     * @return string[]
     */
    public function getAuthorizationGrants()
    {
        return $this->authorizationGrants;
    }

    /**
     * Add an additional Authorization Grant
     *
     * @param string $authorizationGrant
     */
    public function addAuthorizationGrants($authorizationGrant)
    {
        $this->authorizationGrants[] = $authorizationGrant;
    }

    // --

    /**
     * Set the list of scopes
     *
     * @return string[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Add a additional scope
     *
     * @param string $scope
     */
    public function addScope($scope)
    {
        $this->scopes[] = $scope;
    }
}
