<?php

namespace AtelliTech\AdHub\Facebook;

use AtelliTech\AdHub\AbstractService;
use AtelliTech\AdHub\AbstractServiceBuilder;
use Exception;
use Throwable;

/**
 * This component is an API service builder for Facebook.
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class FacebookServiceBuilder extends AbstractServiceBuilder
{
    /**
     * construct
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $apiVersion
     * @return void
     */
    public function __construct(protected string $clientId, protected string $clientSecret, protected string $apiVersion = 'v18.0')
    {}

    /**
     * create service after create google ads client
     *
     * @param array<string, mixed> $options
     * @return AbstractService
     */
    public function create(array $options): AbstractService
    {
        $accessToken = $options['accessToken'] ?? null;
        if (empty($accessToken))
            throw new Exception('accessToken is required.');

        return new FacebookService(new FacebookClient($accessToken, $this->apiVersion));
    }
}