<?php
/*
 * Copyright (C) 2021 Tray Digita
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace TrayDigita\ThemeUpdater\Adapter;

use TrayDigita\ThemeUpdater\AbstractAdapter;
use function sprintf;
use function untrailingslashit;

/**
 * @property-read string $git_base_url
 * @property-read string $owner
 * @property-read string $repository
 * @property-read string $branch
 */
abstract class GitRepositoryUrl extends AbstractAdapter
{
    // github
    // {owner}/{repository}/archive/refs/heads/{branch}.zip
    // gitlab
    // {owner}/{repository}/-/archive/master/([last_position_branch_or_empty]-)?master.zip
    /**
     * @var string
     */
    protected $git_base_url = '';

    /**
     * @var string the username of git
     */
    protected $owner        = '';

    /**
     * @var string eg: on gitlab subgroup please define groupname/{group-if-exists)/project
     */
    protected $repository   = '';

    /**
     * @var string Git branch default master
     */
    protected $branch       = '';

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getRepository(): string
    {
        return $this->repository;
    }

    /**
     * @param string $repository
     */
    public function setRepository(string $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getBranch(): string
    {
        return $this->branch;
    }

    /**
     * @param string $branch
     */
    public function setBranch(string $branch)
    {
        $this->branch = $branch;
    }

    public function getRepositoryUrl() : string
    {
        $branch = $this->getBranch();
        return sprintf(
            '%1$s/%2$s/%3$s%4$s',
            untrailingslashit($this->git_base_url),
            $this->getOwner(),
            $this->getRepository(),
            $branch ? sprintf(
                '/tree/%1$s',
                $branch
            ) : ''
        );
    }

    protected function onProcess()
    {
        // TODO: Implement onProcess() method.
    }
}
