<?php
namespace NightWatch\Command\Factory;

use GuzzleHttp\Client;
use NightWatch\Client\Factory\Packagist as PackagistFactory;
use NightWatch\Client\Factory\Gitlab as GitlabFactory;
use NightWatch\Command\WatchCommand;

class Watch
{
    public function __invoke($config)
    {
        return new WatchCommand(
            (new PackagistFactory)->create(),
            new GitlabFactory,
            $config
        );
    }
}
