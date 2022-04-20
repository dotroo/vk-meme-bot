<?php

namespace App\VKBundle\Controller;

use App\VKBundle\Enum\CallbackTypeEnum;
use App\VKBundle\Handler\NewMessageHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VkCallbackController
{
    const VK_OK = 'ok';

    /** @var string */
    protected string $vkConfirmationKey;

    /** @var NewMessageHandler */
    protected NewMessageHandler $newMessageHandler;

    /** @var LoggerInterface  */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     * @param string $vkConfirmationKey
     * @param NewMessageHandler $newMessageHandler
     */
    public function __construct(LoggerInterface $logger, string $vkConfirmationKey, NewMessageHandler $newMessageHandler) {

        $this->vkConfirmationKey = $vkConfirmationKey;
        $this->newMessageHandler = $newMessageHandler;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $body = $request->getContent();

        $body = json_decode($body, true);

        $this->logger->info('Got new request:', $body);

        switch ($body['type']) {
            case CallbackTypeEnum::CALLBACK_TYPE_CONFIRMATION:
                return new Response($this->vkConfirmationKey);
            case CallbackTypeEnum::CALLBACK_TYPE_NEW_MESSAGE:
                $this->newMessageHandler->handle($request);
                break;
            default:
                break;
        }

        return new Response(self::VK_OK);
    }
}