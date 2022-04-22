<?php

namespace App\VKBundle\Handler;

use Symfony\Component\HttpFoundation\Response;

class ConfirmationHandler
{
    /**
     * @var string
     */
    private string $vkConfirmationKey;

    public function __construct(string $vkConfirmationKey)
    {
        $this->vkConfirmationKey = $vkConfirmationKey;
    }

    /**
     * @param Response $response
     * @return void
     */
    public function handle(Response $response): void
    {
        $response->setContent($this->vkConfirmationKey);
    }
}