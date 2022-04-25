<?php

namespace App\VKBundle\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use VK\Client\VKApiClient;
use VK\Exceptions\Api\VKApiMessagesDenySendException;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;

class VkClient
{
    const METHOD_POST = 'POST';

    /** @var VKApiClient  */
    private VKApiClient $apiClient;

    /** @var string  */
    private string $vkAccessKey;

    /**
     * @param string $vkAccessKey
     */
    public function __construct(string $vkAccessKey)
    {
        $this->apiClient = new VKApiClient();
        $this->vkAccessKey = $vkAccessKey;
    }

    /**
     * @return string
     */
    public function getVkAccessKey(): string
    {
        return $this->vkAccessKey;
    }

    /**
     * @return VKApiClient
     */
    public function getApiClient(): VKApiClient
    {
        return $this->apiClient;
    }

    /**
     * @param string $filename
     * @param int $peerId
     * @return array
     * @throws GuzzleException
     * @throws VKApiException
     * @throws VKApiMessagesDenySendException
     * @throws VKClientException
     */
    public function uploadImage(string $filename, int $peerId): array
    {
        $photos = $this->apiClient->photos();
        $uploadAddress = $photos->getMessagesUploadServer(
            $this->vkAccessKey,
            ['peer_id' => $peerId]
        );

        $file = curl_file_create($filename, 'multipart/form-data');
        $body = ['photo' => $file];

        $httpClient = new Client();
        $response = $httpClient->request(
            self::METHOD_POST,
            $uploadAddress,
            [RequestOptions::BODY => json_encode($body)]
        );

        $vkImage = json_decode($response->getBody()->getContents(), true);

        return $photos->saveMessagesPhoto(
            $this->vkAccessKey,
            [
                $vkImage['photo'],
                $vkImage['server'],
                $vkImage['hash']
            ]
        );
    }
}