<?php

namespace AtelliTech\Ads\Facebook;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * This is a http client for Facebook API.
 *
 * @see https://developers.facebook.com/docs/marketing-api/reference
 *
 * @author Eric Huang <eric.huang@atelli.ai>
 */
class FacebookClient
{
    /**
     * @var string
     */
    private $baseUri = 'https://graph.facebook.com';

    /**
     * @var float
     */
    private $defaultTimeout = 30.0;

    /**
     * @var array<string, string>
     */
    private $defaultHeaders = [
        'Content-Type' => 'application/json',
    ];

    /**
     * @var Client
     */
    private $client;

    /**
     * construct.
     *
     * @param array<string, mixed> $options clientId:string, clientSecret:string, version:string, accessToken:string, timeout?:float
     * @return void
     */
    public function __construct(private array $options)
    {
        $names = ['clientId', 'clientSecret', 'version', 'accessToken'];
        foreach ($names as $name) {
            $value = $options[$name] ?? null;
            if (empty($value)) {
                throw new Exception(sprintf('Undefined configuration %s', $name), 404);
            }
        }

        $timeout = $options['timeout'] ?? $this->defaultTimeout;

        // create client
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => $timeout,
        ]);
    }

    /**
     * get options.
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * send request of GET.
     *
     * @param string $path
     * @param array<string, mixed> $options
     * @return array<int|string, mixed>
     */
    public function get(string $path, array $options = []): array
    {
        // combine path
        $reqPath = $this->combinePath($path);

        // check header
        $headers = $options['headers'] ?? [];
        $options['headers'] = $this->getRequestHeaders($headers);

        // check query
        $query = $options['query'] ?? [];
        $options['query'] = $this->composeQueryWithAccessToken($query);

        // send request
        return $this->handleResponse($this->client->request('GET', $reqPath, $options));
    }

    /**
     * send request of common POST.
     *
     * @param string $path
     * @param array<string, mixed> $options
     * @return array<int|string, mixed>
     */
    public function post(string $path, array $options = []): array
    {
        // combine path
        $reqPath = $this->combinePath($path);

        // check header
        $headers = $options['headers'] ?? [];
        $options['headers'] = $this->getRequestHeaders($headers);

        // check query
        $json = $options['json'] ?? [];
        $options['json'] = $this->composeQueryWithAccessToken($json);

        // send request
        return $this->handleResponse($this->client->request('POST', $reqPath, $options));
    }

    /**
     * send request of form POST.
     *
     * @param string $path
     * @param array<string, mixed> $options
     * @return array<int|string, mixed>
     */
    public function formPost(string $path, array $options = []): array
    {
        // combine path
        $reqPath = $this->combinePath($path);

        // check query
        $formParams = $options['form_params'] ?? [];
        $options['form_params'] = $this->composeQueryWithAccessToken($formParams);

        // send request
        return $this->handleResponse($this->client->request('POST', $reqPath, $options));
    }

    /**
     * send request of multipart POST.
     *
     * @param string $path
     * @param array<string, mixed> $options
     * @return array<int|string, mixed>
     */
    public function multipartPost(string $path, array $options = []): array
    {
        // combine path
        $reqPath = $this->combinePath($path);

        // check query
        $multipart = $options['multipart'] ?? [];
        $options['multipart'][] = ['name' => 'access_token', 'contents' => $this->options['accessToken']];

        // send request
        return $this->handleResponse($this->client->request('POST', $reqPath, $options));
    }

    /**
     * send request.
     *
     * @param string $method
     * @param string $path,
     * @param array<string, mixed> $options
     * @throws Exception
     * @return array<int|string, mixed>
     */
    public function request(string $method, string $path, array $options = []): array
    {
        // combine path
        $reqPath = $this->combinePath($path);

        try {
            // send request
            return $this->handleResponse($this->client->request($method, $reqPath, $options));
            // } catch (GuzzleException $e) {
            //     $response = $e->getResponse();
            // throw new Exception($e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Handle the response.
     *
     * @param ResponseInterface $response
     * @throws Exception
     * @return array<int|string, mixed>
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new Exception('Request failed with status code '.$statusCode);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * combine path.
     *
     * @param string $path
     * @return string
     */
    private function combinePath(string $path): string
    {
        return sprintf('/%s/%s', $this->options['version'], ltrim($path, '/'));
    }

    /**
     * get request header.
     *
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private function getRequestHeaders(array $headers): array
    {
        return array_merge($this->defaultHeaders, $headers);
    }

    /**
     * compose query with access token.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function composeQueryWithAccessToken(array $query): array
    {
        return array_merge($query, ['access_token' => $this->options['accessToken']]);
    }
}
