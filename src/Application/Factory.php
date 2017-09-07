<?php

namespace NightWatch\Application;

use NightWatch\Command\Factory\Watch as WatchFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class Factory
{
    public function __invoke($config)
    {
        /** @var Command $nightwatchCommand */
        $nightwatchCommand = (new WatchFactory)($config);
        $nightwatchCommand->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Only shows which packages would have been updated'
        );

        $application = new Application;
        $application->add($nightwatchCommand);
        $application->setDefaultCommand($nightwatchCommand->getName(), true);

        return $application;
    }
}
