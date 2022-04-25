<?php

namespace App\ImgurBundle\Client;

use Imgur\Client;

class ImgurApiClient
{
    /** @var Client  */
    private Client $client;

    /**
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $this->client = new Client();
        $this->client->setOption('client_id', $clientId);
        $this->client->setOption('client_secret', $clientSecret);
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
