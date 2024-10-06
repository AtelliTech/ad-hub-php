<?php

namespace AtelliTech\Ads\Facebook;

use AtelliTech\Ads\AbstractService;
use Throwable;

/**
 * This service is used to access Facebook Resources. Almost returned data are refering to Resource class of Facebook API.
 *
 * @see https://developers.facebook.com/docs/marketing-api/reference
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class FacebookService extends AbstractService
{
    /**
     * create service.
     *
     * @param array<string, mixed> $config
     * @return static
     */
    public static function create(array $config): static
    {
        return new static(new FacebookClient($config));  // @phpstan-ignore-line
    }

    /**
     * Get service name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Facebook';
    }

    /**
     * list accessible businesses.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function listAccessibleBusinesses(array $params = []): array
    {
        $path = '/me/businesses';

        $defaultQuery = ['fields' => 'id,name'];
        $options = [
            'query' => array_merge($defaultQuery, $params),
        ];

        return $this->client->get($path, $options);
    }

    /**
     * query own ads accounts by particular business id.
     *
     * @param string $id
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function listOwnAdsAccounts(string $id, array $params = []): array
    {
        $path = sprintf('/%s/owned_ad_accounts', $id);

        $defaultQuery = ['fields' => 'id,name'];
        $options = [
            'query' => array_merge($defaultQuery, $params),
        ];

        return $this->client->get($path, $options);
    }

    /**
     * list client ad accounts.
     *
     * @param string $id business id
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function listClientAdAccounts(string $id, array $params = []): array
    {
        $path = sprintf('/%s/client_ad_accounts', $id);

        $defaultQuery = ['fields' => 'id,name'];
        $options = [
            'query' => array_merge($defaultQuery, $params),
        ];

        return $this->client->get($path, $options);
    }

    /**
     * send request.
     *
     * @param string $method
     * @param string $path
     * @param array<string, mixed> $options
     * @return array<int|string, mixed>
     */
    public function request(string $method, string $path, array $options = []): array
    {
        try {
            return $this->client->request($method, $path, $options);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
