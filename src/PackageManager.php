<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is licensed exclusively to Ubeeqo.
 *
 * @copyright   Copyright © 2009-2017 Ubeeqo
 * @license     All rights reserved
 * @author      Matters Studio (https://matters.tech)
 */

namespace NightWatch;

use NightWatch\Client\Gitlab;

abstract class PackageManager
{
    protected $gitHost;

    protected $containerService;

    public function __construct(Gitlab $gitHost, $containerService)
    {
        $this->gitHost = $gitHost;
        $this->containerService = $containerService;
    }

    public function __invoke()
    {
        foreach ($this->getPackages() as $package => $constraint) {
            $currentVersion = $this->getCurrentVersion($package);
            $latestVersion = $this->getLatestVersion($package);

            if ($this->newVersionGreaterButSatisfyConstraint($latestVersion, $currentVersion, $constraint)) {
                $branchName = $this->generateBranchName($package, $latestVersion);

                $this->containerService->updatePackageUsingBranch($package, $latestVersion, $branchName);
                /*
                $this->gitHost->createPullRequest(
                    $branchName,
                    sprintf('Update of %s to version %s', $package,  $latestVersion)
                );
                */
            }
        }
    }

    abstract protected function getPackages() : array;

    abstract protected function getCurrentVersion($package);

    abstract protected function getLatestVersion($package);

    abstract protected function newVersionGreaterButSatisfyConstraint(
        $latestVersion,
        $currentVersion,
        $constraint
    ) : bool;

    abstract protected function generateBranchName($package, $latestVersion) : string;
}