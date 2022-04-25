<?php

namespace App\VKBundle\Handler;

use App\Console\Worker\NewMessageWorker;
use App\VKBundle\Controller\VkCallbackController;
use App\VKBundle\Helper\VkObjectValidator;
use GearmanClient;
use Symfony\Component\HttpFoundation\Response;

class NewMessageHandler
{
    private const EXPECTED_PARAMS = ['text', 'from_id'];

    /** @var GearmanClient  */
    private GearmanClient $gearmanClient;

    public function __construct(GearmanClient $gearmanClient)
    {
        $this->gearmanClient = $gearmanClient;
    }

    /**
     * @param array $body
     * @param Response $response
     * @return void
     */
    public function handle(array $body, Response $response): void
    {
        VkObjectValidator::validateObject(self::EXPECTED_PARAMS, $body);

        $this->gearmanClient->doBackground(NewMessageWorker::QUEUE_NAME, json_encode($body));

        $response->setContent(VkCallbackController::VK_OK);
    }
}