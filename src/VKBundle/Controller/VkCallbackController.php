<?php

namespace App\VKBundle\Controller;

use App\VKBundle\Enum\CallbackTypeEnum;
use App\VKBundle\Exception\VkCallbackException;
use App\VKBundle\Handler\ConfirmationHandler;
use App\VKBundle\Handler\NewMessageHandler;
use App\VKBundle\Helper\VkObjectValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VkCallbackController
{
    public const VK_OK = 'ok';

    private const EXPECTED_PARAMS = ['group_id', 'secret', 'type'];

    /** @var LoggerInterface  */
    private LoggerInterface $logger;

    /** @var int  */
    private int $vkGroupId;

    /** @var string  */
    private string $vkCallbackSecret;

    /** @var NewMessageHandler */
    protected NewMessageHandler $newMessageHandler;

    /** @var ConfirmationHandler */
    private ConfirmationHandler $confirmationHandler;

    /**
     * @param LoggerInterface $logger
     * @param int $vkGroupId
     * @param string $vkCallbackSecret
     * @param ConfirmationHandler $confirmationHandler
     * @param NewMessageHandler $newMessageHandler
     */
    public function __construct(
        LoggerInterface $logger,
        int $vkGroupId,
        string $vkCallbackSecret,
        ConfirmationHandler $confirmationHandler,
        NewMessageHandler $newMessageHandler
    ) {
        $this->logger = $logger;
        $this->vkGroupId = $vkGroupId;
        $this->vkCallbackSecret = $vkCallbackSecret;
        $this->newMessageHandler = $newMessageHandler;
        $this->confirmationHandler = $confirmationHandler;
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

        $response = new Response();

        try {
            VkObjectValidator::validateObject(self::EXPECTED_PARAMS, $body);

            $this->validateRequest($body['group_id'], $body['secret']);

            switch ($body['type']) {
                case CallbackTypeEnum::CALLBACK_TYPE_CONFIRMATION:
                    $this->confirmationHandler->handle($response);
                    break;
                case CallbackTypeEnum::CALLBACK_TYPE_NEW_MESSAGE:
                    if (!isset($body['object']['action'])) {
                        $this->newMessageHandler->handle($body['object']['message'], $response);
                    } else {
                        $response->setContent(self::VK_OK);
                    }
                    break;
                default:
                    $response->setContent(self::VK_OK);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $body);
            $response->setContent($e->getMessage())->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $response;
    }

    /**
     * @param int $groupId
     * @param string $secret
     * @return void
     * @throws VkCallbackException
     */
    protected function validateRequest(int $groupId, string $secret)
    {
        if ($groupId !== $this->vkGroupId || $secret !== $this->vkCallbackSecret) {
            throw new VkCallbackException('Invalid requester');
        }
    }
}