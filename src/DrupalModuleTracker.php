<?php

namespace DrupalModuleTracker;

use Robo\Robo;
use Robo\Config\Config;
use Robo\Common\ConfigAwareTrait;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Robo\Runner as RoboRunner;
use Robo\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;

class DrupalModuleTracker
{

    use ConfigAwareTrait;

    private $runner;

    const NAME = 'DMT';

    const VERSION = '0.0.1-dev';

    public function __construct(Config $config, InputInterface $input = null, OutputInterface $output = null)
    {

        // Create application.
        $this->setConfig($config);
        $application = new Application(self::NAME, self::VERSION);

        // Create and configure container.
        $container = Robo::createDefaultContainer($input, $output, $application, $config);

        $discovery = new CommandFileDiscovery();
        $discovery->setSearchPattern('*Command.php');
        $commandClasses = $discovery->discover(__DIR__ . '/Commands', '\DrupalModuleTracker\Commands');

        // Instantiate Robo Runner.
        $this->runner = new RoboRunner([]);
        $this->runner->setContainer($container);
        $this->runner->registerCommandClasses($application, $commandClasses);
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // Obtain a lock and exit if the command is already running.
        $store = new SemaphoreStore();
        $factory = new Factory($store);
        $lock = $factory->createLock('dmti-command');

        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $statusCode = $this->runner->run($input, $output);

        // Specifically release the lock after successful command invocation.
        $lock->release();

        return $statusCode;
    }
}
