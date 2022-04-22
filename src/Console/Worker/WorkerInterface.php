<?php

namespace App\Console\Worker;

use Symfony\Component\Console\Style\SymfonyStyle;

interface WorkerInterface
{
    /**
     * @param array $workload
     * @param SymfonyStyle $io
     * @return void
     */
    public function run(array $workload, SymfonyStyle $io): void;
}