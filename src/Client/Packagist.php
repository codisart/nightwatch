<?php
namespace NightWatch\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Packagist
{
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    private function buildPackagistUrl($name)
    {
        return "https://packagist.org/packages/" . $name . ".json";
    }

    public function getLatestVersion($name)
    {
        try {
            $result = $this->client->get(
                $this->buildPackagistUrl($name)
            );
        }
        catch(ClientException $e) {
            return null;
        }

        $resultObject = json_decode($result->getBody()->getContents());

        if (!isset($resultObject->package->versions)) {
            return null;
        }
        foreach ($resultObject->package->versions as $key => $value) {
            if (!strstr($value->version, 'dev')) {
                return $value->version;
            }
        }
        return null;
    }
}
