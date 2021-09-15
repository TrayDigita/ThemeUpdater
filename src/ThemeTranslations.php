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

use TrayDigita\ThemeUpdater\Translations\FileTranslation;
use function determine_locale;
use function file_exists;
use function is_dir;
use function is_file;

/**
 * Translations list for current locale
 * @property-read string $text_domain
 * @property-read string $locale
 * @property-read FileTranslation[] $translations
 * @property-read Updater $updater
 * @method FileTranslation|null get(string $name)
 */
class ThemeTranslations extends AbstractTranslations
{
    /**
     * @var string
     */
    protected $text_domain;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array<string, FileTranslation>
     */
    protected $translations = [];

    /**
     * @var Updater
     */
    protected $updater;

    /**
     * @var bool
     */
    private $translated;

    /**
     * @param Updater $updater
     * @param string|null $locale
     *
     * @see \wp_get_pomo_file_data()
     */
    public function __construct(Updater $updater, string $locale = null)
    {
        parent::__construct($updater);
        $this->translated = false;
        $this->updater = $updater;
        $this->text_domain = $updater->getTheme()->get('TextDomain');
        $this->locale = $locale?: determine_locale();
    }

    /**
     * @return Updater
     */
    public function getUpdater(): Updater
    {
        return $this->updater;
    }

    /**
     * @return string
     */
    public function getLocale() : string
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getTextDomain(): string
    {
        return $this->text_domain;
    }

    /**
     * @return FileTranslation[]|null
     */
    public function getTranslations() : array
    {
        if ($this->translated) {
            return $this->translations;
        }
        $this->translated = true;
        $theme = $this->updater->getTheme();
        $stylesheet = $theme->get_stylesheet();
        $textdomain = $theme->get('TextDomain');
        $domainpath = $theme->get('DomainPath');
        $theme_language_path = $theme->get_stylesheet_directory();
        if ($domainpath) {
            $theme_language_path .= $domainpath;
        } else {
            /**
             * @see \WP_Theme::load_textdomain()
             */
            $theme_language_path .= '/languages';
        }

        $locale = $this->getLocale();
        if (file_exists("$theme_language_path/$locale.mo")) {
            $this->translations["$locale.mo"] = new FileTranslation($this, "$theme_language_path/$locale.mo");
        }
        if (file_exists("$theme_language_path/$locale.po")) {
            $this->translations["$locale.po"] = new FileTranslation($this, "$theme_language_path/$locale.po");
        }
        if (is_dir(WP_LANG_DIR)) {
            $dir = WP_LANG_DIR ."/themes/$stylesheet";
            if (is_file("$dir/$textdomain-$locale.po") && is_file("$dir/$textdomain-$locale.mo")) {
                $this->translations["$textdomain-$locale.mo"] = new FileTranslation(
                    $this,
                    "$dir/$textdomain-$locale.mo"
                );
                $this->translations["$textdomain-$locale.po"] = new FileTranslation(
                    $this,
                    "$dir/$textdomain-$locale.po"
                );
            }
        }

        return $this->translations;
    }

    /**
     * Magic method ->$get
     *
     * @param string $name
     *
     * @return FileTranslation|FileTranslation[]|string|Updater|null
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'updater':
                return $this->getUpdater();
            case 'translations':
                return $this->getTranslations();
            case 'locale':
                return $this->getLocale();
            case 'text_domain':
                return $this->getTextDomain();
        }

        return $this->get($name);
    }
}
