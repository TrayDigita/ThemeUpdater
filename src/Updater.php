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

use Psr\Log\LoggerInterface;
use Throwable;
use TrayDigita\ThemeUpdater\Adapter\Noop;
use TrayDigita\ThemeUpdater\Logger\ArrayLogger;
use TrayDigita\ThemeUpdater\Request\Themes;
use WP_Error;
use WP_Theme;
use function _x;
use function array_keys;
use function get_bloginfo;
use function in_array;
use function is_a;
use function is_string;
use function sprintf;
use function uasort;
use function wp_get_theme;

/**
 * @property-read array<string, AbstractAdapter> $adapters
 * @property-read array<int, string> $locked_adapters
 * @property-read array<int, string> $reserved_keywords
 * @property-read WP_Theme $theme
 * @property-read ThemeTranslations $translations
 * @property-read string $wp_version
 * @property-read Result|WP_Error|Throwable|null $processed_result
 */
class Updater
{
    const VERSION = '1.0.0';

    /**
     * @var array<int, string>
     */
    protected $reserved_keywords = [
        'theme',
        'reserved_keywords',
        'adapters',
        'locked_adapters',
    ];

    /**
     * @var array<string, AbstractAdapter>
     */
    protected $adapters = [];

    /**
     * @var array<string, true>
     */
    protected $locked_adapters = [];

    /**
     * @var WP_Theme
     */
    protected $theme;

    /**
     * @var Themes
     */
    protected $theme_request;

    /**
     * @var ThemeTranslations
     */
    protected $translations;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Result|null
     */
    protected $processed_result = null;

    /**
     * @var string
     */
    protected $wp_version = '';

    /**
     * @param WP_Theme|null $theme
     * @param LoggerInterface|null $logger
     * @uses \Psr\Log\NullLogger for totally disable logs, default @uses ArrayLogger
     * @uses wp_get_theme() if $theme is empty
     */
    public function __construct(
        WP_Theme $theme = null,
        LoggerInterface $logger = null
    ) {
        $this->wp_version = get_bloginfo('version');
        $this->theme = $theme ?? wp_get_theme();
        $this->translations = new ThemeTranslations($this);
        $this->theme_request = new Themes($this->translations);
        $this->theme_request->addFromWPTheme($this->theme);
        $this->setLogger($logger??new ArrayLogger());
    }

    /**
     * @return string
     */
    public function getWpVersion(): string
    {
        return $this->wp_version;
    }

    /**
     * @return Result|null
     */
    public function getProcessedResult()
    {
        return $this->processed_result;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger null if remove logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return WP_Theme
     */
    public function getTheme(): WP_Theme
    {
        return $this->theme;
    }

    /**
     * @return ThemeTranslations
     */
    public function getTranslations(): ThemeTranslations
    {
        return $this->translations;
    }

    /**
     * @return Themes
     */
    public function getThemeRequest(): Themes
    {
        return $this->theme_request;
    }

    /**
     * @return array<string>
     */
    public function getReservedKeywords(): array
    {
        return $this->reserved_keywords;
    }

    /**
     * @return array<string, AbstractAdapter>
     */
    public function getAdapters() : array
    {
        return $this->adapters;
    }

    /**
     * @return string[]
     */
    public function getLockedAdapters() : array
    {
        return array_keys($this->locked_adapters);
    }

    /**
     * check if name is reserved
     *
     * @param string|AbstractAdapter $adapter adapter name
     *
     * @return bool
     */
    public function isKeywordReserved($adapter) : bool
    {
        $id = $this->getIdFromAdapterParam($adapter);
        return in_array($id, $this->getReservedKeywords(), true);
    }

    /**
     * @param mixed|AbstractAdapter|string $adapter
     *
     * @return false|string
     */
    final protected function getIdFromAdapterParam($adapter)
    {
        if (is_string($adapter)) {
            return $adapter;
        }
        if ($adapter instanceof AbstractAdapter) {
            return $adapter->getId();
        }

        return false;
    }

    /**
     * @param string|AbstractAdapter $adapter
     *
     * @return bool
     */
    public function has($adapter) : bool
    {
        $id = $this->getIdFromAdapterParam($adapter);
        return $id && isset($this->adapters[$id]);
    }

    /**
     * Lock the adapter
     *
     * @param string|AbstractAdapter $adapter adapter name
     *
     * @return bool true if adapter exists
     */
    public function lock($adapter) : bool
    {
        $id = $this->getIdFromAdapterParam($adapter);
        if ($id === false) {
            return false;
        }
        $this->locked_adapters[$id] = true;
        return true;
    }

    /**
     * @param string|AbstractAdapter $adapter
     *
     * @return bool
     */
    public function isLocked($adapter) : bool
    {
        $id = $this->getIdFromAdapterParam($adapter);
        if ($id === false) {
            return false;
        }
        return $this->locked_adapters[$id]??false;
    }

    /**
     * Get adapter by given name or adapter
     *
     * @param string|AbstractAdapter $adapter
     *
     * @return AbstractAdapter|false
     */
    public function get($adapter)
    {
        $id = $this->getIdFromAdapterParam($adapter);
        if ($id === false) {
            return false;
        }
        return $this->adapters[$id] ?? false;
    }

    /**
     * Set adapter
     *
     * @param AbstractAdapter|string $adapter instance of AbstractAdapter
     *
     * @return AbstractAdapter|WP_Error
     */
    public function set($adapter)
    {
        if (is_string($adapter) && is_a($adapter, AbstractAdapter::class, true)) {
            $adapter = new $adapter($this);
        }

        if (!$adapter instanceof AbstractAdapter) {
            return new WP_Error(
                'tray-digita:theme_updater',
                sprintf(
                    _x(
                        'Argument adapter must be instance of %s',
                        'Theme Updater',
                        'tray-digita-theme-updater'
                    ),
                    AbstractAdapter::class
                ),
                [
                    'adapter' => $adapter
                ]
            );
        }

        $id = $adapter->getId();
        if ($this->isLocked($id)) {
            return new WP_Error(
                'tray-digita:theme_updater',
                sprintf(
                    _x(
                        'Adapter id %s is locked.',
                        'Theme Updater',
                        'tray-digita-theme-updater'
                    ),
                    $id
                ),
                [
                    'adapter' => $adapter
                ]
            );
        }

        if ($this->isKeywordReserved($id)) {
            return new WP_Error(
                'tray-digita:theme_updater',
                sprintf(
                    _x(
                        'Adapter id %s is contain in reserved keyword list.',
                        'Theme Updater',
                        'tray-digita-theme-updater'
                    ),
                    $id
                ),
                [
                    'adapter' => $adapter
                ]
            );
        }

        $this->adapters[$id] = $adapter;
        uasort($this->adapters, [$this, 'sortCallback']);
        return $adapter;
    }

    /**
     * Sorting uasort
     *
     * @param AbstractAdapter $adapter
     * @param AbstractAdapter $secondAdapter
     *
     * @return int
     */
    protected function sortCallback(
        AbstractAdapter $adapter,
        AbstractAdapter $secondAdapter
    ): int {
        $priorA = $adapter->getPriority();
        $priorB = $secondAdapter->getPriority();
        if ($priorA === $priorB) {
            return 0;
        }
        return $priorA < $priorB ? -1 : 1;
    }

    /**
     * Add adapter if exists
     *
     * @param string|AbstractAdapter $adapter
     *
     * @return AbstractAdapter|WP_Error
     */
    public function add($adapter)
    {
        if (is_string($adapter) && is_a($adapter, AbstractAdapter::class, true)) {
            $adapter = new $adapter($this);
        }

        if (!$adapter instanceof AbstractAdapter) {
            return new WP_Error(
                'tray-digita:theme_updater',
                sprintf(
                    _x(
                        'Argument adapter must be instance of %s',
                        'Theme Updater',
                        'tray-digita-theme-updater'
                    ),
                    AbstractAdapter::class
                ),
                [
                    'adapter' => $adapter
                ]
            );
        }

        $id = $adapter->getId();
        if (!isset($this->adapters[$id])) {
            return $this->set($adapter);
        }
        return new WP_Error(
            'tray-digita:theme_updater',
            sprintf(
                _x(
                    'Adapter id %s is exists.',
                    'Theme Updater',
                    'tray-digita-theme-updater'
                ),
                $id
            ),
            [
                'adapter' => $this->adapters[$id]
            ]
        );
    }

    /**
     * @todo implement hook registration
     */
    public function register()
    {
        // @todo completion
    }

    /**
     * @param bool $force
     *
     * @return Result
     */
    public function update(bool $force = false) : Result
    {
        if (!$force && $this->processed_result) {
            return $this->processed_result;
        }
        foreach ($this->getAdapters() as $adapter) {
            if ($force || $adapter->isProcessed()) {
                $adapter->process();
            }
            $response = $adapter->getLastResult();
            if ($response instanceof Result) {
                if ($response->isValid()) {
                    $this->processed_result = $response;
                    return $response;
                } elseif (!$this->processed_result && $response instanceof Noop) {
                    $this->processed_result = $response;
                }
            }
        }

        if (!$this->processed_result) {
            $this->processed_result = (new Noop($this))->process();
        }

        return $this->processed_result;
    }

    /**
     * Magic method getter
     *
     * @param string $name
     *
     * @return false|string[]|AbstractAdapter|AbstractAdapter[]|Result|ThemeTranslations|WP_Theme
     *      returning WP_Theme if key is theme
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'theme':
                return $this->getTheme();
            case 'translations':
                return $this->getTranslations();
            case 'reserved_keywords':
                return $this->getReservedKeywords();
            case 'locked_adapters':
                return $this->getLockedAdapters();
            case 'adapters':
                return $this->getAdapters();
            case 'wp_version':
                return $this->getWpVersion();
            case 'processed_result':
                return $this->getProcessedResult();
        }

        return $this->get($name);
    }
}
