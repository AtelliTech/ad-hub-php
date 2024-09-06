<?php

namespace AtelliTech\AdHub\Facebook;

use Exception;
use GuzzleHttp\Client;
use Throwable;

/**
 * This is a http client for Facebook API.
 *
 * @see https://developers.facebook.com/docs/marketing-api/reference
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class FacebookClient
{
    /**
     * @var string
     */
    const BASE_URI = 'https://graph.facebook.com';

    /**
     * construct
     *
     * @param string $accessToken
     * @param string $apiVersion
     * @return void
     */
    public function __construct(protected string $accessToken, protected string $apiVersion = 'v18.0')
    {}

    /**
     * default header
     *
     * @return array<string, string>
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * send request to Facebook API
     *
     * @param string $method
     * @param string $path
     * @param array<string, mixed> $options
     * @return array<string|int, mixed>
     */
    public function request(string $method, string $path, array $options = []): array
    {
        $endpoint = sprintf('/%s/%s', $this->apiVersion, ltrim($path, '/'));
        $client = new Client([
            'base_uri' => self::BASE_URI,
            'timeout' => 30.0,
        ]);

        // check header
        $headers = $options['headers'] ?? [];
        $options['headers'] = array_merge(['headers'=>$this->getDefaultHeaders()], $headers);

        // check method and attach access token
        if (strtoupper($method) === 'GET') {
            $options['query'] = array_merge(['access_token' => $this->accessToken], $options['query'] ?? []);
        } elseif (strtoupper($method) === 'POST') {
            if (isset($options['form_params']) && is_array($options['form_params']))
                $options['form_params'] = array_merge(['access_token' => $this->accessToken], $options['form_params']);
            elseif (isset($options['json']) && is_array($options['json']))
                $options['json'] = array_merge(['access_token' => $this->accessToken], $options['json']);
            elseif (isset($options['multipart'] ) && is_array($options['multipart']))
                $options['multipart'][] = ['name' => 'access_token', 'contents' => $this->accessToken];
            else
                throw new Exception('Parameter error of mentod POST');
        } else {
            throw new Exception('Method not allowed.');
        }

        // send request
        try {
            $response = $client->request($method, $endpoint, $options);
        } catch (Throwable $e) {
            echo "\nMessage: " . $e->getMessage();
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return json_decode((string) $response->getBody(), true);
    }
}
