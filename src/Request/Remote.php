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

namespace TrayDigita\ThemeUpdater\Request;

use TrayDigita\ThemeUpdater\Updater;
use function is_string;
use function sprintf;
use function wp_remote_request;

/**
 * @todo Completion
 */
class Remote
{
    protected $url;
    protected $options = [];
    protected $default_user_agent = '';

    public function __construct(string $url, array $options = [])
    {
        $this->default_user_agent = sprintf(
            'WordPress/%1$s () %2$s/%3$s',
            get_bloginfo('version'),
            'ThemeUpdater',
            Updater::VERSION,
            get_bloginfo('url')
        );
        $this->url = $url;
        $this->options = $options;
        if (!isset($this->options['method']) || ! is_string($this->options['method'])) {
            $this->options['method'] = 'GET';
        }
    }

    public function send()
    {
        $options = [];
        wp_remote_request(
            $this->url
        );
    }
}
