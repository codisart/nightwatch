<?php
namespace NightWatch\Command;

use GuzzleHttp\Client;
use NightWatch\Client\Gitlab as GitlabClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('scan')
            ->setDescription('Get packages to watch.')
            ->setHelp('This command allows you to scan the packages to watch.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        var_dump((new GitlabClient(new Client))->getComposerLockFile());
        $output->writeln([
            'Scan',
            '============',
            '',
        ]);
    }
}
