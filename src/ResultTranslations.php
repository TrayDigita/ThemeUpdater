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

namespace TrayDigita\ThemeUpdater;

/**
 * @property-read Result $result
 */
class ResultTranslations extends AbstractTranslations
{
    /**
     * @var Result
     */
    protected $result;

    /**
     * @param Updater $updater
     * @param Result $result
     */
    public function __construct(Updater $updater, Result $result)
    {
        parent::__construct($updater);
        $this->result = $result;
    }

    /**
     * @return Result
     */
    public function getResult(): Result
    {
        return $this->result;
    }
}
