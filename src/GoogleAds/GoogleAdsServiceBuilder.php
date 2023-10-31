<?php

namespace AtelliTech\AdHub\GoogleAds;

use AtelliTech\AdHub\AbstractService;
use AtelliTech\AdHub\AbstractServiceBuilder;
use Exception;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsClientBuilder;

/**
 * This class is a service build for GoogleAds
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class GoogleAdsServiceBuilder extends AbstractServiceBuilder
{
    /**
     * construct
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $developToken
     * @param string $apiVersion default: latest
     */
    public function __construct(protected string $clientId, protected string $clientSecret, protected string $developToken, protected string $apiVersion = 'latest')
    {}

    /**
     * Create GoogleAdsService by particular customerId and refreshToken
     *
     * @param array<string,mixed> $config [(int)$customerId, (string)$refreshToken]
     * @return GoogleAdsService
     */
    public function create(array $config): GoogleAdsService
    {
        try {
            $customerId = $config['customerId'] ?? null;
            $refreshToken = $config['refreshToken'] ?? null;

            if ($customerId === null)
                throw new Exception("Undefined configuration customerId", 404);

            if ($refreshToken === null)
                throw new Exception("Undefined configuration refreshToken", 404);

            $oAuth2Credential  = (new OAuth2TokenBuilder)->withClientId($this->clientId)
                                                         ->withClientSecret($this->clientSecret)
                                                         ->withRefreshToken($refreshToken)
                                                         ->build();

            $googleAdsClient = (new GoogleAdsClientBuilder)->withOAuth2Credential($oAuth2Credential)
                                                           ->withDeveloperToken($this->developToken)
                                                           ->withLoginCustomerId($customerId)
                                                           ->build();

            return new GoogleAdsService($googleAdsClient);
        } catch (Exception $e) {
            throw $e;
        }
    }
}