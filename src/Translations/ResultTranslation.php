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

namespace TrayDigita\ThemeUpdater\Translations;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use TrayDigita\ThemeUpdater\AbstractTranslation;
use TrayDigita\ThemeUpdater\ResultTranslations;
use TrayDigita\ThemeUpdater\TranslationMetadata;
use function in_array;
use function is_string;

/**
 * @property-read string $type
 * @property-read string $slug
 * @property-read string $language
 * @property-read string $version
 * @property-read string $updated
 * @property-read string $package
 * @property-read bool $autoupdate
 * @property-read ResultTranslations $translations
 * @method ResultTranslations getTranslations()
 */
class ResultTranslation extends AbstractTranslation
{
    const TYPE = 'theme';

    /**
     * @param ResultTranslations $translations
     * @param string $locale
     * @param string $version
     * @param $updated_date
     * @param string|null $package_url
     * @param bool $autoupdate
     */
    public function __construct(
        ResultTranslations $translations,
        string $locale,
        string $version,
        $updated_date,
        string $package_url = null,
        bool $autoupdate = false
    ) {
        parent::__construct($translations);
        if (is_string($updated_date)) {
            try {
                if (trim($updated_date) !== '') {
                    $updated_date = new DateTimeImmutable(trim($updated_date));
                } else {
                    $updated_date = '';
                }
            } catch (Exception $e) {
                $updated_date = '';
            }
        }

        if ($updated_date instanceof DateTimeInterface) {
            $updated_date = $updated_date->format(self::DATE_FORMAT);
        } elseif (is_int($updated_date)) {
            $updated_date = date(self::DATE_FORMAT, $updated_date);
        } elseif (! is_string($updated_date)) {
            $updated_date = '';
        }

        $this->metadata = new TranslationMetadata(
            $this,
            [
                'type' => ! in_array(static::TYPE, ['theme', 'plugin']) ? self::TYPE : static::TYPE,
                'slug' => $translations->updater->theme->get_stylesheet(),
                'language' => $locale,
                'version' => $version,
                'updated' => $updated_date,
                'package' => $package_url?:'',
                'autoupdate' => $autoupdate
            ]
        );
    }

    /**
     * @return TranslationMetadata
     */
    public function getMetadata(): TranslationMetadata
    {
        return $this->metadata;
    }

    /**
     * @param string $name
     *
     * @return bool|string|null
     */
    public function get(string $name): string
    {
        return $this->metadata[$name]??'';
    }

    /**
     * @param string $name
     *
     * @return ResultTranslations|bool|string|TranslationMetadata|null
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'translations':
                return $this->getTranslations();
            case 'metadata':
                return $this->getMetadata();
        }

        return $this->get($name);
    }
}
