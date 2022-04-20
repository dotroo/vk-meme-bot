<?php

namespace App\VKBundle\Client;

use VK\Client\VKApiClient;

class VkClient
{
    private VKApiClient $apiClient;
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
}