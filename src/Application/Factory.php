<?php
namespace NightWatch\Application;

use NightWatch\Command\ScanCommand;
use NightWatch\Command\Factory\Watch as WatchFactory;
use Symfony\Component\Console\Application;

final class Factory
{
    public function create($config)
    {
        $application = new Application;
        $application->add(new ScanCommand);
        $application->add((new WatchFactory)->create($config));
        return $application;
    }
}
