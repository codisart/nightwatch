<?php
namespace NightWatch\Service\Package;

use NightWatch\Client\Gitlab;
use NightWatch\Container\Instance;
use NightWatch\Container\Manager as ContainerManager;

class Update {

    private $containerManager;

    private $gitlabClient;

    public function __construct(ContainerManager $containerManager,Gitlab $gitlabClient)
    {
        $this->containerManager = $containerManager;
        $this->gitlabClient = $gitlabClient;
    }

    public function updatePackageUsingBranch($package, $version, $branchName)
    {
        if ($this->gitlabClient->doesBranchExist($branchName)) {
            echo sprintf('The branch %s already exists' . "\n", $branchName);
            echo '====================================' . "\n";
            return;
        }

        /** @var Instance $container */
        $container  = $this->containerManager->get('composer:7.1');

        $container->runScript($package, $branchName);

        /*
        echo 'Create pull request' . "\n";
        echo '====================================' . "\n";
        $this->gitlabClient->createPullRequest(
            $branchName,
            sprintf('Update of %s to version %s', $package,  $version),
            'This pull request update automatically a composer dependency following the requirement of composer.json'
        );
        */
    }

    public function __invoke($project, $package, $version)
    {
        $branchName = sprintf('update-%s-%s', str_replace('/', '-', $package), $version);

        if ($this->gitlabClient->doesBranchExist($branchName)) {
            return;
        }

        try {
            /** @var Instance $container */
            $container  = $this->containerManager->get($project);

            $container->runScript($package, $branchName);

            /*
            echo 'Create pull request' . "\n";
            echo '====================================' . "\n";
            $this->gitlabClient->createPullRequest(
                $branchName,
                sprintf('Update of %s to version %s', $package,  $version),
                'This pull request update automatically a composer dependency following the requirement of composer.json'
            );
            */
        }
        catch(\Exception $exception) {
            var_dump($exception);
        }
    }


}
