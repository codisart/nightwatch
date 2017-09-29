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

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use NightWatch\Client\Gitlab;
use NightWatch\Client\Packagist;

class Composer extends PackageManager
{
    /**
     * @var Packagist
     */
    private $packagistClient;

    /**
     * Composer constructor.
     * @param Gitlab $gitHost
     * @param Packagist $packagistClient
     * @param $containerService
     */
    public function __construct(Gitlab $gitHost, Packagist $packagistClient, $containerService)
    {
        parent::__construct($gitHost, $containerService);
        $this->packagistClient = $packagistClient;
    }

    protected function getPackages() : array
    {
        $composerJson = $this->gitHost->getComposerJsonFile();

        return array_merge(
            (array) $composerJson->require,
            (array) $composerJson->{'require-dev'}
        );
    }

    protected function getCurrentVersion($package)
    {
        $lockedPackages = $this->gitHost->getComposerLockedPackages();

        foreach ($lockedPackages as $lockedPackage) {
            if (strtolower($lockedPackage->name) === strtolower($package)) {
                return $lockedPackage->version;
            }
        }
        return null;
    }

    protected function getLatestVersion($package)
    {
        return $this->packagistClient->getLatestVersion($package);
    }

    /**
     * @param $latestVersion
     * @param $currentVersion
     * @param $constraint
     * @return bool
     */
    protected function newVersionGreaterButSatisfyConstraint(
        $latestVersion,
        $currentVersion,
        $constraint
    ): bool
    {
        return !empty($latestVersion)
            && !empty($currentVersion)
            && Semver::satisfies($latestVersion, $constraint)
            && Comparator::greaterThan($latestVersion, $currentVersion);
    }

    /**
     * @param $package
     * @param $latestVersion
     * @return array
     */
    protected function generateBranchName($package, $latestVersion): string
    {
        return sprintf('update-%s-%s', str_replace('/', '-', $package), $latestVersion);
    }
}