<?php

namespace Raml\SecurityScheme\SecuritySettings;

class OAuth1SecuritySettings
{
    const TYPE = 'OAuth 1.0';

    // --

    /**
     * The URI of the Temporary Credential Request endpoint as defined in RFC5849 Section 2.1
     *
     * @var string
     */
    private $requestTokenUri;

    /**
     * The URI of the Resource Owner Authorization endpoint as defined in RFC5849 Section 2.2
     *
     * @var string
     */
    private $authorizationUri;

    /**
     * The URI of the Token Request endpoint as defined in RFC5849 Section 2.3
     *
     * @var string
     */
    private $tokenCredentialsUri;

    // ---

    /**
     * Get the Token Credentials URI
     *
     * @return string
     */
    public function getTokenCredentialsUri()
    {
        return $this->tokenCredentialsUri;
    }

    /**
     * Set the Token Credentials URI
     *
     * @param string $tokenCredentialsUri
     */
    public function setTokenCredentialsUri($tokenCredentialsUri)
    {
        $this->tokenCredentialsUri = $tokenCredentialsUri;
    }

    // --

    /**
     * Get the Request Token URI
     *
     * @return string
     */
    public function getRequestTokenUri()
    {
        return $this->requestTokenUri;
    }

    /**
     * Set the Request Token URI
     *
     * @param string $requestTokenUri
     */
    public function setRequestTokenUri($requestTokenUri)
    {
        $this->requestTokenUri = $requestTokenUri;
    }

    // --

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
}
