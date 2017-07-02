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
      die('plop');
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

            $requiredPackages = array_merge(
                (array) $requiredPackages->require,
                (array) $requiredPackages->{'require-dev'}
            );

            foreach ($requiredPackages as $package => $requiredVersion) {
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
                    try {
                        $this->cloneProject($project['gitlab']['clone_url']);
                        $this->updateComposerPackage($package);

                        $branchName = sprintf('update-%s-%s', str_replace('/', '-', $package), $latestVersion);
                        $this->createBranch($branchName);
                        $this->commitUpdate();
                        $this->pushUpdate($branchName);

                        $gitlabClient->createPullRequest(
                          $branchName,
                          sprintf('Update of %s to version %s', $package,  $latestVersion),
                          'This pull request update automatically a composer dependency following the requirement of composer.json'
                        );
                    }
                    catch(\Exception $e) {
                        $this->clearTempFolderPath();
                        $this->output->writeln([$e->getMessage()]);
                        if ($e->getPrevious()) {
                            $this->output->writeln([$e->getPrevious()->getMessage()]);
                        }
                    }
                }
            }
        }
    }

    private function clearTempFolderPath() {
        $tempFolderPath = dirname(__DIR__, 2) . '/tmp';

        try {
            $this->runCommand('rm -rf ' . $tempFolderPath);
        }
        catch (\RuntimeException $e) {
            throw new \Exception(sprintf('The clone of the project failed.', null, $e));
        }
    }

    private function getTempFolderPath() {
        $tempFolderPath = dirname(__DIR__, 2) . '/tmp';

        if (!is_dir($tempFolderPath)) {
            if(!mkdir($tempFolderPath)) {
                throw new \Exception(sprintf('Can not create tempory folder at the location %s.', $tempFolderPath));
            }
        }
        return $tempFolderPath;
    }

    private function cloneProject($cloneUrl)
    {
        chdir($this->getTempFolderPath());

        try {
            $this->runCommand('git clone ' . $cloneUrl . ' project');
        }
        catch (\RuntimeException $e) {
            throw new \Exception('The clone of the project failed.', null, $e);
        }

        chdir($this->getTempFolderPath() . '/project');
    }

    private function updateComposerPackage($packageName)
    {
        try {
            $this->runCommand('composer update --quiet ' . $packageName);
        }
        catch (\RuntimeException $e) {
            throw new \Exception('Update of package failed.', null, $e);
        }
    }

    private function createBranch($branchName)
    {
        $this->output->writeln(['Create branch']);
        try {
            $this->runCommand('git checkout -b ' . $branchName);
        }
        catch (\RuntimeException $e) {
            throw new \Exception('Failed branch creation.', null, $e);
        }
    }

    private function commitUpdate()
    {
        try {
            $this->runCommand('git commit --all --no-verify -m "UD-0000 nightwatch"');
        }
        catch (\RuntimeException $e) {
            throw new \Exception('Nothing to commit.', null, $e);
        }
    }

    private function pushUpdate($branchName)
    {
        try {
            $this->runCommand('git push --no-verify -u origin ' . $branchName);
        }
        catch (\RuntimeException $e) {
            throw new \Exception('Nothing to push.', null, $e);
        }
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
