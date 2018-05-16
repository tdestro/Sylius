<?php

declare(strict_types=1);

namespace DestroBundle\Factory\FileSystem;

class GoogleCloudStorageServiceFactory
{
    public static function createService($credentials)
    {
        $client = new \Google_Client();
        $client->setApplicationName('DestroMachinesStore');
        $client->setScopes([\Google_Service_Storage::CLOUD_PLATFORM]);
        $client->setAuthConfig($credentials);

        $service = new \Google_Service_Storage($client);

        return $service;
    }
}
