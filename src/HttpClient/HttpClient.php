<?php
/*
 * This file is part of the EmiViada CloudFlare package.
 *
 * Emiliano Viada <emjovi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EmiViada\CloudFlare\HttpClient;

use GuzzleHttp\Client;
use EmiViada\CloudFlare\CloudFlare;

/**
 * @author Emiliano Viada <emjovi@gmail.com>
 */
class HttpClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Init client
     *
     * @param array $options
     */
    public function init(array $options)
    {
        $headers = [
            'User-Agent' => $options['user_agent'],
            'X-Auth-Key' => $options['api_key'],
            'X-Auth-Email' => $options['email'],
            'Content-Type' => $options['content_type'],
        ];
        if (version_compare(Client::VERSION, '6.0') >= 0) {
            $this->client = new Client([
                'base_uri' => CloudFlare::API_BASE_URL,
                'timeout' => $options['timeout'],
                'headers' => $headers,
            ]);
        } else {
            $this->client = new Client([
                'base_url' => CloudFlare::API_BASE_URL,
                'timeout' => $options['timeout'],
                'defaults' => [
                    'headers' => $headers,
                ],
            ]);
        }
    }

    /**
     * Send a request to the API.
     *
     * @param string $path
     * @param string $method
     * @param string $body
     * @param array $parameters
     * @param array $headers
     *
     * @return string $response content
     */
    public function request($path, $method = 'GET', $body = null, array $parameters = null, array $headers = [])
    {
        try {
            // Guzzle <6.0 BC
            if (version_compare(Client::VERSION, '6.0') >= 0) {
                $response = $this->client->request($method, $path, [
                    'body'      => $body,
                    'query'     => $parameters,
                    'headers'   => $headers,
                ]);
            } else {
                $request = $this->client->createRequest($method, $path, [
                    'body'      => $body,
                    'query'     => $parameters,
                    'headers'   => $headers,
                ]);
                $response = $this->client->send($request);
            }
        } catch (ClientException $e) {
            $cloudFlareError = json_decode($e->getResponse()->getBody()->getContents(), true)['errors'][0];
            throw new ClientException($e->getMessage(), $e->getCode(), $cloudFlareError['message'], $cloudFlareError['code']);
        }

        return $response->getBody()->getContents();
    }

    /**
     * Send a GET request.
     *
     * @param string $path
     * @param array  $parameters
     * @param array  $headers
     *
     * @return string $response content
     */
    public function get($path, array $parameters = [], array $headers = [])
    {
        // Fix CloudFlare API compliant parameters
        array_walk_recursive($parameters, function (&$item, $key) {
            if (is_bool($item)) {
                $item = true === $item ? 'true' : 'false';
            }
        });

        return $this->request($path, 'GET', null, $parameters, $headers);
    }

    /**
     * Send a POST request.
     *
     * @param string $path
     * @param mixed $body
     * @param array $headers
     *
     * @return string $response content
     */
    public function post($path, $body = null, array $headers = [])
    {
        return $this->request($path, 'POST', $body, null, $headers);
    }

    /**
     * Send a PUT request.
     *
     * @param string $path
     * @param mixed $body
     * @param array $headers
     *
     * @return string The response content
     */
    public function put($path, $body, array $headers = [])
    {
        return $this->request($path, 'PUT', $body, null, $headers);
    }

    /**
     * Send a PATCH request.
     *
     * @param string $path
     * @param mixed $body
     * @param array $headers
     *
     * @return string The response content
     */
    public function patch($path, $body, array $headers = [])
    {
        return $this->request($path, 'PATCH', $body, null, $headers);
    }

    /**
     * Send a DELETE request.
     *
     * @param string $path
     * @param mixed $body
     * @param array $headers
     *
     * @return string $response content
     */
    public function delete($path, $body = null, array $headers = [])
    {
        return $this->request($path, 'DELETE', $body, null, $headers);
    }
}
