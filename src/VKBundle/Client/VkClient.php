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
    const VK_API_VERSION = '5.131';
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
        $this->apiClient = new VKApiClient(self::VK_API_VERSION);
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

        $file = new \CURLFile($filename, 'image/jpeg', $filename . '.jpg');

//        $body = ['photo' => $file];

//        $httpClient = new Client();
//        $response = $httpClient->request(
//            self::METHOD_POST,
//            $uploadAddress['upload_url'],
//            [
//                RequestOptions::BODY => json_encode($body),
//                RequestOptions::HEADERS => ['Content-Type' => 'multipart/form-data']
//            ]
//        );

//        $vkImage = json_decode($response->getBody()->getContents(), true);

        $ch=curl_init();
        curl_setopt_array($ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $uploadAddress['upload_url'],
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array("photo" => $file),
            ]
        );
        $vkImage = curl_exec($ch);
        $vkImage = json_decode($vkImage, true);

        return $photos->saveMessagesPhoto(
            $this->vkAccessKey,
            [
                'photo' => $vkImage['photo'],
                'server' => $vkImage['server'],
                'hash' => $vkImage['hash'],
            ]
        );
    }
}