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

namespace NightWatch\Project;

use NightWatch\Client\Gitlab as GitlabClient;

class Composer
{
    /**
     * @var array
     */
    private $lockedPackages;

    private $gitlabClient;

    public function __construct(GitlabClient $gitlabClient)
    {
        $this->gitlabClient = $gitlabClient;
    }

    public function getRequiredPackages()
    {
        $requiredPackages = $this->gitlabClient->getComposerRequiredPackages();

        return array_merge(
            (array) $requiredPackages->require,
            (array) $requiredPackages->{'require-dev'}
        );
    }

    public function getLockedVersion($package)
    {
        $this->lockedPackages = $this->gitlabClient->getComposerLockedPackages();

        foreach ($this->lockedPackages as $lockedPackage) {
            if (strtolower($lockedPackage->name) === strtolower($package)) {
                return $lockedPackage->version;
            }
        }
        return null;
    }
}