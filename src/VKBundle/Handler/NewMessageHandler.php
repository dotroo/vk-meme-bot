<?php

namespace App\VKBundle\Handler;

use App\VKBundle\Client\VkClient;
use Symfony\Component\HttpFoundation\Request;

class NewMessageHandler
{
    /**
     * @var VkClient
     */
    private VkClient $apiClient;

    /**
     * @param VkClient $apiClient
     */
    public function __construct(VkClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function handle(Request $request): void
    {
        $messages = $this->apiClient->getApiClient()->messages();

        $messages->send($this->apiClient->getVkAccessKey(), );
    }
}