<?php

namespace Raml\SecurityScheme\SecuritySettings;

use Raml\SecurityScheme\SecuritySettingsInterface;

class OAuth1SecuritySettings implements SecuritySettingsInterface
{
    const TYPE = 'OAuth 1.0';

    // --

    /**
     * The URI of the Temporary Credential Request endpoint as defined in RFC5849 Section 2.1
     *
     * @var string
     */
    private $tokenCredentialsUri;

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
    private $requestTokenUri;

    // ---
    // SecuritySettingsInterface

    /**
     * Flesh out the settings
     *
     * @param array                     $data
     * @param SecuritySettingsInterface $sourceSettings
     *
     * @throws \Exception
     *
     * @return OAuth1SecuritySettings
     */
    public static function createFromArray(array $data, SecuritySettingsInterface $sourceSettings = null)
    {
        if ($sourceSettings && !$sourceSettings instanceof OAuth1SecuritySettings) {
            throw new \Exception();
        }

        $settings = $sourceSettings ? clone $sourceSettings : new static();

        if (isset($data['tokenCredentialsUri'])) {
            $settings->setTokenCredentialsUri($data['tokenCredentialsUri']);
        }

        if (isset($data['requestTokenUri'])) {
            $settings->setRequestTokenUri($data['requestTokenUri']);
        }

        if (isset($data['authorizationUri'])) {
            $settings->setAuthorizationUri($data['authorizationUri']);
        }


        return $settings;
    }

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
