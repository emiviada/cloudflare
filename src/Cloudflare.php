<?php
/*
 * This file is part of the EmiViada CloudFlare package.
 *
 * Emiliano Viada <emjovi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EmiViada\Cloudflare;

use Symfony\Component\OptionsResolver\OptionsResolver;
use EmiViada\Cloudflare\HttpClient\HttpClient;

/**
 *
 * @author Emiliano Viada <emjovi@gmail.com>
 */
class Cloudflare
{
    const API_BASE_URL = 'https://api.cloudflare.com/client/v4/';

    const VALID_DNS_RECORD_TYPES = [
        'A' => 'A',
        'AAAA' => 'AAAA',
        'CNAME' => 'CNAME',
        'TXT' => 'TXT',
        'SRV' => 'SRV',
        'LOC' => 'LOC',
        'MX' => 'MX',
        'NS' => 'NS',
        'SPF' => 'SPF',
    ];

    const VALID_CACHE_LEVELS = [
        'aggressive' => 'aggressive',
        'basic' => 'basic',
        'simplified' => 'simplified'
    ];

    const MIN_TTL = 120;
    const MAX_TTL = 2147483647;

    /**
     * @var
     */
    private $httpClient;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);

        $this->httpClient = new HttpClient();
        $this->httpClient->init($this->options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        // Defaults
        $resolver->setDefaults([
            'user_agent' => 'emiviada-cloudflare-sdk (https://github.com/emiviada/cloudflare)',
            'content_type' => 'application/json',
            'timeout' => 10,
        ]);
        // Required
        $resolver->setRequired([
            'email',
            'api_key',
        ]);
        $resolver->setAllowedTypes('user_agent', 'string');
        $resolver->setAllowedTypes('timeout', 'int');
        $resolver->setAllowedTypes('email', 'string');
        $resolver->setAllowedTypes('api_key', 'string');
    }

    // USER STUFF
    // TODO: Implement

    // ZONE STUFF
    /**
     * @param array $parameters
     * @param array $headers
     */
    public function getZones(array $parameters = [], array $headers = [])
    {
        return $this->httpClient->get('zones', $parameters, $headers);
    }

    // ZONE SETTINGS STUFF
    /**
     * @param string $zoneId
     * @param string $setting
     * @param array $parameters
     * @param array $headers
     */
    private function getSetting($zoneId = null, $setting = '', array $parameters = [], array $headers = [])
    {
        if (is_null($zoneId)) {
            throw new \Exception("You must provide a zone identifier (zoneId).");
        }

        return $this->httpClient->get('zones/' . $zoneId . '/settings/' . $setting, $parameters, $headers);
    }

    /**
     * @param string $zoneId
     * @param array $parameters
     * @param array $headers
     */
    public function getCacheLevel($zoneId = null, array $parameters = [], array $headers = [])
    {
        return $this->getSetting($zoneId, 'cache_level', $parameters, $headers);
    }

    /**
     * @param string $zoneId
     * @param array $parameters
     * @param array $headers
     */
    public function getMinify($zoneId = null, array $parameters = [], array $headers = [])
    {
        return $this->getSetting($zoneId, 'minify', $parameters, $headers);
    }

    /**
     * @param string $zoneId
     * @param mixed $body
     * @param array $headers
     */
    public function setCacheLevel($zoneId = null, $body = null, array $headers = [])
    {
        if (is_null($zoneId)) {
            throw new \Exception("You must provide a zone identifier (zoneId).");
        }

        if (!isset($body['value']) || !in_array($body['value'], self::VALID_CACHE_LEVELS)) {
            throw new \Exception("You must provide one of the following values: aggressive, basic or simplified.");
        }

        return $this->httpClient->patch('zones/' . $zoneId . '/settings/cache_level', json_encode($body), $headers);
    }

    /**
     * @param string $zoneId
     * @param mixed $body
     * @param array $headers
     */
    public function setMinify($zoneId = null, $body = null, array $headers = [])
    {
        if (is_null($zoneId)) {
            throw new \Exception("You must provide a zone identifier (zoneId).");
        }

        return $this->httpClient->patch('zones/' . $zoneId . '/settings/minify', json_encode($body), $headers);
    }


    // DNS RECORDS STUFF
    /**
     * @param string $zoneId
     * @param array $parameters
     * @param array $headers
     */
    public function getDnsRecords($zoneId = null, array $parameters = [], array $headers = [])
    {
        if (is_null($zoneId)) {
            throw new \Exception("You must provide a zone identifier (zoneId).");
        }

        return $this->httpClient->get('zones/' . $zoneId . '/dns_records', $parameters, $headers);
    }

    /**
     * @param string $zoneId
     * @param mixed $body
     * @param array $headers
     */
    public function createDnsRecord($zoneId = null, $body = null, array $headers = [])
    {
        if (is_null($zoneId)) {
            throw new \Exception("You must provide a zone identifier (zoneId).");
        }

        if (!isset($body['type']) || !isset($body['name']) || !isset($body['content'])) {
            throw new \Exception("You must provide the following data: type, name and content.");
        }

        if (!in_array($body['type'], self::VALID_DNS_RECORD_TYPES)) {
            throw new \Exception("The dns record type is not valid.");
        }

        return $this->httpClient->post('zones/' . $zoneId . '/dns_records', json_encode($body), $headers);
    }

    /**
     * @param string $zoneId
     * @param string $recordId
     */
    public function getDnsRecordDetails($zoneId = null, $recordId = null)
    {
        if (is_null($zoneId) || is_null($recordId)) {
            throw new \Exception("You must provide a zone identifier (zoneId) and a dns record identifier ($recordId).");
        }

        return $this->httpClient->get('zones/' . $zoneId . '/dns_records/' . $recordId);
    }

    /**
     * @param string $zoneId
     * @param string $recordId
     * @param mixed $body
     * @param array $headers
     */
    public function updateDnsRecord($zoneId = null, $recordId = null, $body = null, array $headers = [])
    {
        if (is_null($zoneId) || is_null($recordId)) {
            throw new \Exception("You must provide a zone identifier (zoneId) and a dns record identifier ($recordId).");
        }

        if (!isset($body['content'])) {
            throw new \Exception("You must provide the following data: content.");
        }

        if (isset($body['type']) && !in_array($body['type'], self::VALID_DNS_RECORD_TYPES)) {
            throw new \Exception("The dns record type is not valid.");
        }

        if (isset($body['ttl']) && $body['ttl'] !== 1 &&
            !((self::MIN_TTL <= $body['ttl']) && ($body['ttl'] <= self::MAX_TTL))) {
                throw new \Exception("Invalid TTL. Must be between 120 and 2,147,483,647 seconds, or 1 for automatic");
        }

        return $this->httpClient->put('zones/' . $zoneId . '/dns_records/' . $recordId, json_encode($body), $headers);
    }

    /**
     * @param string $zoneId
     * @param string $recordId
     */
    public function deleteDnsRecord($zoneId = null, $recordId = null)
    {
        if (is_null($zoneId) || is_null($recordId)) {
            throw new \Exception("You must provide a zone identifier (zoneId) and a dns record identifier ($recordId).");
        }

        return $this->httpClient->delete('zones/' . $zoneId . '/dns_records/' . $recordId);
    }
}
