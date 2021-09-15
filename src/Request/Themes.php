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

use TrayDigita\ThemeUpdater\ThemeTranslations;
use WP_Theme;
use function array_keys;
use function array_merge;
use function array_unique;
use function json_encode;
use const JSON_UNESCAPED_SLASHES;

class Themes
{
    /**
     * @var ThemeTranslations
     */
    protected $translations;

    /**
     * @var Theme[]
     */
    protected $themes = [];

    /**
     * @param ThemeTranslations $translations
     */
    public function __construct(ThemeTranslations $translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return ThemeTranslations
     */
    public function getTranslations(): ThemeTranslations
    {
        return $this->translations;
    }

    /**
     * @return Theme[]
     */
    public function getThemes(): array
    {
        return $this->themes;
    }

    public function addFromWPTheme(WP_Theme $theme) : bool
    {
        return $this->add(Theme::fromWPTheme($theme));
    }

    public function setFromWPTheme(WP_Theme $theme)
    {
        $this->set(Theme::fromWPTheme($theme));
    }

    public function add(Theme $theme) : bool
    {
        if (isset($this->themes[$theme->getSlug()])) {
            return false;
        }
        $this->set($theme);
        return true;
    }

    public function set(Theme $theme)
    {
        $this->themes[$theme->slug] = $theme;
    }

    /**
     * @return array
     */
    public function getTranslationRequest() : array
    {
        $textDomain = $this->translations->getTextDomain();
        $locale = $this->translations->getLocale();
        $translations = [];
        foreach ($this->translations->getTranslations() as $fileTranslation) {
            if (!isset($translations[$textDomain])) {
                $translations[$textDomain] = [];
            }
            /*$language = $fileTranslation->get('Language');
            if ($language && \preg_match('~^(?:[a-z]+[_-][A-Z]+|[a-zA-Z\-]+)$~', $language)) {
                $language = \str_replace('-', '_', $language);
            } else {
                $language = '';
            }*/
            $translations[$textDomain][$locale] = [
                'POT-Creation-Date' => $fileTranslation->get('POT-Creation-Date'),
                'PO-Revision-Date' => $fileTranslation->get('PO-Revision-Date'),
                'Project-Id-Version' => $fileTranslation->get('Project-Id-Version'),
                'X-Generator' => $fileTranslation->get('X-Generator'),
                'Language' => $fileTranslation->get('Language'),
            ];
        }
        return $translations;
    }

    /**
     * @param Theme|null $activeTheme
     *
     * @return array
     */
    public function getThemesRequest(Theme $activeTheme = null) : array
    {
        $themesResponse = [
            'active' => $activeTheme ? $activeTheme->getSlug() : '',
            'themes' => []
        ];
        foreach ($this->themes as $theme) {
            $themesResponse['themes'][$theme->getSlug()] = $theme->toRequestTheme();
        }

        return $themesResponse;
    }

    /**
     * @return string[]
     */
    public function getLocaleRequest() : array
    {
        $locales = [];
        foreach ($this->getTranslationRequest() as $textDomain => $themes) {
            foreach ($themes as $locale => $item) {
                $locales[$locale] = true;
            }
        }
        return array_keys($locales);
    }

    /**
     * @param Themes|null $themes
     * @param Theme|null $activeTheme
     *
     * @return array<string, array>
     */
    public function getRequestBodyArray(Themes $themes = null, Theme $activeTheme = null) : array
    {
        $data = [
            'translations' => $this->getTranslationRequest(),
            'themes' => $this->getThemesRequest($activeTheme),
            'locale' => $this->getLocaleRequest(),
        ];
        if ($themes) {
            $response = $themes->getRequestBodyArray(null, $activeTheme);
            foreach ($themes->getTranslationRequest() as $textDomain => $item) {
                foreach ($item as $locale => $data) {
                    $data['translations'][$textDomain][$locale] = $data;
                }
            }
            $themeData = $themes->getThemesRequest($activeTheme);
            $data['themes']['active'] = $themeData['active'];
            foreach ($themeData['themes'] as $key => $item) {
                $data['themes'][$key] = $item;
            }
            $data['locale'] = array_values(
                array_unique(
                    array_merge(
                        $data['locale'],
                        $themes->getLocaleRequest()
                    )
                )
            );
        }
        // to reduce size even though just very small (JSON_UNESCAPED_SLASHES)
        return $data;
    }

    /**
     * @param Themes|null $themes
     * @param Theme|null $activeTheme
     *
     * @return array<string, string> value as json_encode();
     */
    public function getRequestBody(Themes $themes = null, Theme $activeTheme = null) : array
    {
        $data = [];
        foreach ($this->getRequestBodyArray($themes, $activeTheme) as $key => $item) {
            $data[$key] = json_encode($key, JSON_UNESCAPED_SLASHES);
        }
        return $data;
    }
}
