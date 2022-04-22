<?php

namespace App\Console\Worker;

use Symfony\Component\Console\Style\SymfonyStyle;

class NewMessageWorker implements WorkerInterface
{
    const QUEUE_NAME = 'new.message';
    /**
     * @inheritDoc
     */
    public function run(array $workload, SymfonyStyle $io): void
    {
        // TODO: Implement run() method.
    }
}