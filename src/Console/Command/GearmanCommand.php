<?php

namespace App\Console\Command;

use App\Console\Worker\WorkerInterface;
use GearmanJob;
use GearmanWorker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GearmanCommand extends Command
{
    /**
     * @var GearmanWorker
     */
    private GearmanWorker $worker;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(GearmanWorker $worker, ContainerInterface $container)
    {
        $this->worker = $worker;
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('gearman:worker')
            ->setDescription('Start gearman worker')
            ->setDefinition(new InputDefinition([
                new InputOption(
                    'task',
                    't',
                    InputOption::VALUE_REQUIRED,
                    'Worker task'
                ),
                new InputOption(
                    'max_exec_time',
                    'm',
                    InputOption::VALUE_REQUIRED,
                    'Maximum execution time',
                    0
                ),
            ]));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = time();
        $task = $input->getOption('task');
        $class = explode('.', (string)$task);
        $class = array_map(
            function ($value) {
                return ucfirst($value);
            },
            $class
        );

        $workerName = 'App\\Console\\Worker\\' . implode('', $class) . 'Worker';
        $task = strtolower($task);

        if (!class_exists($workerName)) {
            throw new \RuntimeException('Worker "' . $workerName . '" not found');
        }

        $io = new SymfonyStyle($input, $output);

        $this->worker->addFunction($task, function (GearmanJob $job) use ($workerName, $io) {
            $data = json_decode($job->workload(), true);
            if (!empty($data)) {
                /** @var WorkerInterface $taskWorker */
                $taskWorker = $this->container->get($workerName);
                $taskWorker->run($data, $io);
            }
        });

        $maxExecTime = (int)$input->getOption('max_exec_time');
        $io->title('Starting worker ' . $workerName);
        while ($this->worker->work()) {
            if ($maxExecTime > 0 && $maxExecTime + $startTime >= time()) {
                $io->success(sprintf('Stopped worker after %d seconds', $maxExecTime));
                break;
            }
        }

    }
}