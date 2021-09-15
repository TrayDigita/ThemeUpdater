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
use TrayDigita\ThemeUpdater\Result;
use TrayDigita\ThemeUpdater\Updater;

/**
 * No operation adapter for theme updater
 * @method Result process()
 */
class Noop extends AbstractAdapter
{
    protected $version = Updater::VERSION;

    /**
     * @var int set to the highest value
     */
    protected $priority = 1000;

    /**
     * @inheritDoc
     */
    protected function onConstruct()
    {
        $this->name = _x(
            'No operation adapter.',
            'Theme Updater',
            'tray-digita-theme-updater'
        );
        $this->description = _x(
            'Adapter to handle empty adapter.',
            'Theme Updater',
            'tray-digita-theme-updater'
        );
    }

    protected function onProcess(): Result
    {
        // use empty data that determine used theme headers
        // and detected as invalid
        return new Result($this, []);
    }
}
