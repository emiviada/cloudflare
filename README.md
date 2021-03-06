# CloudFlare

CloudFlare PHP SDK to communicate with [CloudFlare API  v4](https://api.cloudflare.com/).

Under development. Use it as **your own risks!**

## Installation

Clone the repository and run composer update.

## Example

The below code snippet is what you will find under the example folder:

```php
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use EmiViada\Cloudflare\Cloudflare;

$options = [
    'email' => 'your@email.com',
    'api_key' => 'YourAPIkEYHere'
];
$api = new Cloudflare($options);
$responseZones = json_decode($api->getZones(array('name' => 'midomain.com')), true);
if ($responseZones['success']) {
    $zone = $responseZones['result'][0];
    $zoneId = $zone['id'];

    // Create new dns record
    $dnsCreate = $api->createDnsRecord($zoneId, array(
        'type' => Cloudflare::VALID_DNS_RECORD_TYPES['CNAME'],
        'name' => 'cf-test',
        'content' => 'midomain.com'
    ));

    $recordId = $dnsCreate['result']['id'];

    // Update dns record
    $dnsUpdate = $api->updateDnsRecord($zoneId, $recordId, [
        'type' => Cloudflare::VALID_DNS_RECORD_TYPES['TXT'],
        'name' => 'cf-test-updated',
        'locked' => true,
        'ttl' => 220,
        'content' => 'midomain.com'
    ]);

    // List dns records
    $dnsResponse = $api->getDnsRecords($zoneId, array('per_page' => 30));

    // Delete dns record
    $dnsDelete = $api->deleteDnsRecord($zoneId, $recordId);
}
```
