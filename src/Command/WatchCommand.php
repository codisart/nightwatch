<?php
namespace NightWatch\Command;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use NightWatch\Client\Packagist as PackagistClient;
use NightWatch\Client\Factory\Gitlab as GitlabFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WatchCommand extends Command
{
    private $client;

    private $packagistClient;

    private $gitlabFactory;

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
        $this->output = $output;
        $this->output->writeln([
            'Watch',
            '============',
            '',
        ]);

        foreach ($this->projects as $projectName => $project) {
            $gitlabClient = $this->gitlabFactory->create(
                $project['gitlab']['api_base_url'],
                $project['gitlab']['project_id'],
                $project['gitlab']['private_token']
            );

            $requiredPackages = $gitlabClient->getComposerRequiredPackages();
            $lockedPackages = $gitlabClient->getComposerLockedPackages();

            foreach ($requiredPackages->require as $package => $requiredVersion) {
                $lockedVersion = null;
                foreach ($lockedPackages as $lockedPackage) {
                    if (strtolower($lockedPackage->name) == strtolower($package)) {
                        $lockedVersion = $lockedPackage->version;
                    }
                }

                $latestVersion = $this->packagistClient->getLatestVersion($package);

                if (isset($lockedVersion)
                    && isset($latestVersion)
                    && Semver::satisfies($latestVersion, $requiredVersion)
                    && Comparator::greaterThan($latestVersion, $lockedVersion)
                ) {
                    var_dump($package, $lockedVersion, $latestVersion);
                    // $this->updatePackage($package);
                }
            }
        }
    }

    private function updatePackage($name)
    {
        chdir(dirname(__DIR__, 2) . '/tmp/svc-psp');
        $this->output->writeln(getcwd());

        $branchName = 'update-' . str_replace('/', '-', $name);

        try {
            $this->runCommand('git checkout . && git checkout master');
        }
        catch (\RuntimeException $e) {
            $this->output->writeln(['Worktree cleaned']);;
        }

        $this->output->writeln(['Create branch']);
        try {
            $this->runCommand('git checkout -b ' . $branchName);
        }
        catch (\RuntimeException $e) {
            $this->output->writeln(['Branch created']);;
        }

        $this->runCommand('composer update --quiet ' . $name);

        try {
            $this->runCommand('git commit --all --no-verify -m "UD-0000 nightwatch"');
        }
        catch (\RuntimeException $e) {
            $this->output->writeln(['Nothing to commit']);;
        }

        try {
            $this->runCommand('git push --no-verify -u origin ' . $branchName);
        }
        catch (\RuntimeException $e) {
            $this->output->writeln(['Nothing to push']);;
        }

        $this->gitlabClient->createPullRequest($branchName);
    }

    private function runCommand($command)
    {
        exec($command, $output, $returnValue);
        if ($returnValue !== 0) {
            throw new \RuntimeException(implode("\r\n", $output));
        }
        return $output;
    }
}
