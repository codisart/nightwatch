<?php
namespace Sentinel\Application;

use Symfony\Component\Console\Application;
use Sentinel\Command\ScanCommand;
use Sentinel\Command\Factory\Watch as WatchFactory;

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
