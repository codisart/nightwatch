<?php
namespace NightWatch\Command;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use NightWatch\Client\Packagist as PackagistClient;
use NightWatch\Client\Factory\Gitlab as GitlabFactory;
use NightWatch\Client\Gitlab as GitlabClient;
use NightWatch\Container\Manager;
use NightWatch\Package;
use NightWatch\Project\Composer;
use NightWatch\Service\Package\Update;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WatchCommand extends Command
{
    private $packagistClient;

    private $gitlabFactory;

    /**
     * @var array
     */
    private $projects;

    /**
     * @var array
     */
    private $lockedPackages;

    public function __construct(
        PackagistClient $packagistClient,
        GitlabFactory $gitlabFactory,
        $projects
    ) {
        $this->packagistClient = $packagistClient;
        $this->gitlabFactory = $gitlabFactory;
        $this->projects = $projects;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('watch')
            ->setDescription('Check version of packages to watch.')
            ->setHelp('This command allows you to check the versions of the packages watched.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Watch',
            '============',
        ]);

        $containerManager = new Manager;

        foreach ($this->projects as $project) {
            $gitlabConfig = $project['gitlab'];

            $gitlabClient = $this->gitlabFactory->create(
                $gitlabConfig['api_base_url'],
                $gitlabConfig['project_id'],
                $gitlabConfig['private_token']
            );

            $updateService = new Update(
                $containerManager,
                $gitlabClient
            );

            $composer = new Composer(
                $gitlabClient
            );

            $requiredPackages = $composer->getRequiredPackages();

            foreach ($requiredPackages as $package => $requiredVersion) {
                $lockedVersion = $composer->getLockedVersion($package);
                $latestVersion = $this->packagistClient->getLatestVersion($package);

                if (!isset($lockedVersion, $latestVersion)) {
                    continue;
                }

                if (Semver::satisfies($latestVersion, $requiredVersion)
                    && Comparator::greaterThan($latestVersion, $lockedVersion)
                ) {
                    $updateService('composer:7.1', $package, $latestVersion);
                }
            }
        }

        $containerManager->cleanUp();
    }
}
