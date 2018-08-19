<?php

declare(strict_types=1);

namespace DestroBundle\Factory\FileSystem;

use GuzzleHttp\Client;
class GoogleCloudStorageServiceFactory
{
    public static function createService($credentials)
    {

        $client = new \Google_Client();
        $guzzleClient = new Client(['base_uri' => $client->getConfig('base_path'), 'force_ip_resolve' => 'v4', 'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ]]);
        $client->setHttpClient($guzzleClient);
        $client->setApplicationName('DestroMachinesStore');
        $client->setScopes([\Google_Service_Storage::CLOUD_PLATFORM]);
        $client->setAuthConfig($credentials);

        $service = new \Google_Service_Storage($client);

        return $service;
    }
}
