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

use WP_Theme;
use function is_string;
use function property_exists;
use function str_replace;
use function strtolower;

/**
 * Update check contains body
 * [
        * 'translations' => json_encode(wp_get_installed_translations( 'themes' )),
        * 'themes' => json_encode([
            * 'active' => $theme->get_stylesheet(),
            * 'themes' => [
                * $theme->get_stylesheet() => [ // this to check the theme slug
                    * 'Name'       => $theme->get( 'Name' ),
                    * 'Title'      => $theme->get( 'Name' ),
                    * 'Version'    => $theme->get( 'Version' ),
                    * 'Author'     => $theme->get( 'Author' ),
                    * 'Author URI' => $theme->get( 'AuthorURI' ),
                    * 'Template'   => $theme->get_template(),
                    * 'Stylesheet' => $theme->get_stylesheet(),
                * ],
            * ]
        * ]),
        * 'locale' => json_encode(array_values( get_available_languages() ))
    * ];
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $title
 * @property-read string $version
 * @property-read string $author
 * @property-read string $authoruri
 * @property-read string $template
 * @property-read string $stylesheet
 */
class Theme
{
    protected $slug;
    protected $name = '';
    protected $title = '';
    protected $version = '';
    protected $author = '';
    protected $authoruri = '';
    protected $template = '';
    protected $stylesheet = '';

    /**
     * @param string $slug slug is $theme->get_stylesheet()
     *
     * @param array $data
     */
    public function __construct(string $slug, array $data)
    {
        foreach ($data as $key => $item) {
            if (! is_string($key)) {
                continue;
            }
            $this->__set($key, (string) $item);
        }

        $this->slug = $slug;
        $this->name = $this->name?:($this->title??'');
        $this->title = $this->title?:($this->name??'');
        $this->stylesheet = $this->stylesheet?:($this->slug??'');
        $this->template = $this->template?:($this->stylesheet??'');
    }

    /**
     * @param WP_Theme $theme
     *
     * @return Theme
     */
    public static function fromWPTheme(WP_Theme $theme): Theme
    {
        $data = [
            'name' => $theme->get('Name'),
            'title' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'author' => $theme->get('Author'),
            'authoruri' => $theme->get('AuthorURI'),
            'template' => $theme->get_template(),
            'stylesheet' => $theme->get_stylesheet(),
        ];
        return new static($theme->get_stylesheet(), $data);
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getAuthorURI(): string
    {
        return $this->authoruri;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getStylesheet(): string
    {
        return $this->stylesheet;
    }

    public function toRequestTheme() : array
    {
        return [
            'Name'       => $this->name,
            'Title'      => $this->title,
            'Version'    => $this->version,
            'Author'     => $this->author,
            'Author URI' => $this->authoruri,
            'Template'   => $this->template,
            'Stylesheet' => $this->stylesheet,
        ];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function __set(string $name, string $value)
    {
        $name= strtolower(str_replace(' ', '', $name));
        $this->$name = $value;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function __get(string $name)
    {
        $lower = $name;
        switch ($lower) {
            case 'authoruri':
            case 'author_uri':
                return $this->getAuthorURI();
        }
        return property_exists($this, $name)
            ? $this->$name
            : ($this->$lower??'');
    }
}
