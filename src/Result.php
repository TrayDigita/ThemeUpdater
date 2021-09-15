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

use ArrayAccess;
use TrayDigita\ThemeUpdater\Adapter\Noop;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function array_values;
use function explode;
use function get_site_transient;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function strtolower;
use function substr;
use function version_compare;

/**
 * Result Update
 * @property-read AbstractAdapter $adapter
 * @property-read Updater $updater
 * @property-read string $name
 * @property-read string $author
 * @property-read string $author_url
 * @property-read string $version
 * @property-read string $package
 * @property-read string[] $tags
 * @property-read string $require_wp
 * @property-read string $require_php
 * @property-read string $theme_url
 * @property-read ResultTranslations $translations
 */
class Result implements ArrayAccess
{
    /**
     * @var array<string, string>
     */
    protected $theme_factory_keys = [
        'name'        => 'Name',
        'description' => 'Description',
        'theme_url'   => 'ThemeURI',
        'author'      => 'Author',
        'author_url'  => 'AuthorURI',
        'require_wp'  => 'RequiresWP',
        'require_php' => 'RequiresPHP',
        'tags'        => 'Tags',
    ];

    /**
     * @var array<string, string>
     */
    protected $factory_keys = [
        'description' => 'description',

        'name' => 'name',
        'title' => 'name',
        'theme_name' => 'name',
        'theme_title' => 'name',

        'theme_url' => 'theme_url',
        'theme_uri' => 'theme_url',
        'themeuri' => 'theme_url',
        'themeurl' => 'theme_url',

        'author' => 'author',
        'author_name' => 'author',
        'authorname' => 'author',

        'author_url' => 'author_url',
        'author_uri' => 'author_url',
        'authoruri' => 'author_url',
        'authorurl' => 'author_url',

        'version' => 'version',
        'theme_version' => 'version',
        'new_version' => 'version',

        'package' => 'package',
        'package_url' => 'package',
        'zip_url' => 'package',
        'tag' => 'tags',

        'require_wp'  => 'require_wp',
        'requirewp'  => 'require_wp',
        'requireswp'  => 'require_wp',
        'requires_wp'  => 'require_wp',
        'requiredwp'  => 'require_wp',
        'required_wp'  => 'require_wp',
        'requireswordpress'  => 'require_wp',
        'requires_wordpress'  => 'require_wp',
        'requirewordpress'  => 'require_wp',
        'require_wordpress'  => 'require_wp',
        'requiredwordpress'  => 'require_wp',
        'required_wordpress'  => 'require_wp',

        'requiresphp' => 'require_php',
        'requires_php' => 'require_php',
        'requirephp' => 'require_php',
        'require_php' => 'require_php',
        'requiredphp' => 'require_php',
        'required_php' => 'require_php',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * @var array<string, mixed>
     */
    protected $original_data = [];

    /**
     * @var bool determine valid if theme name & version exists
     */
    protected $valid = false;

    /**
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * @var ResultTranslations
     */
    protected $translations;

    /**
     * @param AbstractAdapter $adapter
     * @param array $data append theme header and merge with data,
     *                   if empty array, use theme headers as value
     * @param ResultTranslations|null $translations
     */
    public function __construct(
        AbstractAdapter $adapter,
        array $data,
        ResultTranslations $translations = null
    ) {
        $this->adapter = $adapter;
        $updater = $adapter->getUpdater();
        $this->translations = $translations??new ResultTranslations(
            $updater,
            $this
        );
        $this->original_data = $data;
        $theme = $updater->getTheme();
        foreach ($data as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $newKey = $this->normalizeKey($key);
            $this->data[$key] = $this->normalizeValue($newKey, $value);
        }
        $this->valid = ! $adapter instanceof Noop
            && !empty($this->data['name'])
            && !empty($this->data['version']);

        foreach ($this->theme_factory_keys as $key => $item) {
            if (!is_string($key)) {
                continue;
            }
            $key              = $this->normalizeKey($key);
            if (isset($this->data[$key])) {
                continue;
            }
            $this->data[$key] = $this->normalizeValue($key, $theme->get($item));
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return array|string
     */
    protected function normalizeValue(string $key, $value)
    {
        $key = $this->normalizeKey($key);
        switch ($key) {
            case 'name':
            case 'theme_url':
            case 'version':
            case 'author_url':
            case 'description':
            case 'author':
            case 'package':
            case 'require_wp':
            case 'require_php':
                if (is_object($value) && method_exists($value, '__tostring')) {
                    return (string) $value;
                }
                return is_string($value) ? $value : '';
            case 'tags':
                if (!$value) {
                    if (is_string($value)) {
                        return array_values(array_map('trim', explode(',', $value)));
                    }
                    if (is_array($value)) {
                        return array_values(array_map('trim', array_filter($value, 'is_string')));
                    }
                }
                return [];
        }

        return $value;
    }

    /**
     * @return ResultTranslations
     */
    public function getTranslations(): ResultTranslations
    {
        return $this->translations;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return bool
     */
    public function isReadyUpdate() : bool
    {
        $package = $this->get('package');
        $package = $package && substr($package, -4) === '.zip';
        return $package && $this->isNeedUpdate();
    }

    /**
     * Check if need update
     *
     * @return bool
     */
    public function isNeedUpdate() : bool
    {
        $version = $this->updater->getTheme()->get('Version');
        return is_string($version) && version_compare($version, $this->getVersion());
    }

    /**
     * @return AbstractAdapter
     */
    public function getAdapter(): AbstractAdapter
    {
        return $this->adapter;
    }

    /**
     * @return string[]
     */
    public function getFactoryKeys(): array
    {
        return $this->factory_keys;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOriginalData(): array
    {
        return $this->original_data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get('name');
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->get('author');
    }

    /**
     * @return string
     */
    public function getAuthorUrl(): string
    {
        return (string) $this->get('author_url');
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return (string) $this->get('version');
    }

    /**
     * @return string
     */
    public function getThemeUrl(): string
    {
        return $this->get('theme_url');
    }

    /**
     * @return string
     */
    public function getPackage(): string
    {
        return (string) $this->get('package');
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return (array) $this->get('tags');
    }

    /**
     * @return string
     */
    public function getRequireWp(): string
    {
        return $this->get('require_wp');
    }

    /**
     * @return string
     */
    public function getRequirePhp(): string
    {
        return $this->get('require_php');
    }

    /**
     * @param string $offset
     *
     * @return string
     */
    public function normalizeKey(string $offset) : string
    {
        return $this->factory_keys[$offset]??(
                $this->factory_keys[strtolower($offset)] ?? $offset
            );
    }


    public function getDefaultResultTransient() : array
    {
        $theme = $this->adapter->getUpdater()->getTheme();
        $stylesheet = $theme->get_stylesheet();
        $package  = get_site_transient('update_themes');
        $response = null;
        $no_update = null;
        if (!is_object($package)) {
            $response  = $package ? ($package->response ?? null) : null;
            $response  = is_array($response) ? $response : [];
            $no_update = $package ? ($package->no_update ?? null) : null;
            $no_update = is_array($no_update) ? $no_update : [];
        }
        $current = $response && isset($response[$stylesheet])
            ? $response[$stylesheet]
            : ($no_update && $no_update[$stylesheet]
                ? $no_update[$stylesheet]
                : []
            );
        return [
            'theme'        => $stylesheet,
            'new_version'  => !empty($current['new_version'])
                        ? ($current['new_version']?:$theme->get('Version'))
                        : $theme->get('Version'),
            'url'          => !empty($current['url'])
                ? ($current['url']?:$theme->get('ThemeURI'))
                : $theme->get('ThemeURI'),
            'package'      => $current['package']??$this->getPackage(),
            'requires'     => $current['requires']??$theme->get('RequiresWP'),
            'requires_php' => $current['requires_php']??$theme->get('RequiresPHP')
        ];
    }

    /**
     * Data for default update transient
     *
     * @return array
     */
    public function getResultTransient() : array
    {
        $theme = $this->adapter->getUpdater()->getTheme();
        $default = $this->getDefaultResultTransient();
        $result = [
            'theme'        => $theme->get_stylesheet(),
            'new_version'  => $this->getVersion(),
            'url'          => $this->getThemeUrl(),
            'package'      => $this->getPackage(),
            'requires'     => $this->getRequireWp(),
            'requires_php' => $this->getRequirePhp(),
        ];
        $result = array_merge($default, $result);
        $result = apply_filters(
            'tray-digita:theme_updater:result_transient',
            $result,
            $this
        );

        return is_array($result)
            ? array_merge($default, $result)
            : $default;
    }

    /**
     * @param string $name
     *
     * @return mixed|string
     */
    public function get(string $name)
    {
        return $this->data[$this->normalizeKey($name)] ?? '';
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name) : bool
    {
        $name = $this->normalizeKey($name);
        return array_key_exists($name, $this->data);
    }

    public function offsetExists($offset) : bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
        // pass
    }

    /**
     * @param string $name
     *
     * @return mixed|AbstractAdapter|null
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'adapter':
                return $this->getAdapter();
            case 'translations':
                return $this->getTranslations();
            case 'data':
                return $this->getData();
        }
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        // pass
    }
}
