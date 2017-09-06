<?php
namespace NightWatch\Application;

use NightWatch\Command\Factory\Watch as WatchFactory;
use Symfony\Component\Console\Application;

class Factory
{
    public function __invoke($config)
    {
        $nightwatchCommand = (new WatchFactory)($config);

        $application = new Application;
        $application->add($nightwatchCommand);
        $application->setDefaultCommand($nightwatchCommand->getName(), true);

        return $application;
    }
}
