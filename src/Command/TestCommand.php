<?php

namespace NightWatch\Command;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use GuzzleHttp\Client;
use NightWatch\Client\Factory\Gitlab;
use NightWatch\Client\Packagist;
use NightWatch\Container\Manager;
use NightWatch\Service\Package\Update;
use NightWatch\Service\Project\Composer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    private $lockedPackages;

    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Check version of packages to watch.')
            ->setHelp('This command allows you to check the versions of the packages watched.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = [
            'svc-psp' => [
                'composer' => [
                    'php' => '7.1',
                ],
            ]
        ];

        

        $containerManager = new Manager;

        foreach ($projects as $project) {
            $gitlabClient = (new Gitlab())->create(
                'https://git.ubeeqo.com',
                '75',
                '72f-eNGBb6oBRFMSVuZ7'
            );

            if ($project['composer']) {
                $updateService = new Update(
                    $containerManager,
                    $gitlabClient
                );

                $composer = new \NightWatch\Composer(
                    $gitlabClient,
                    new Packagist(new Client()),
                    $updateService
                );
                $composer();
            }
        }

        $containerManager->cleanUp();
    }
}