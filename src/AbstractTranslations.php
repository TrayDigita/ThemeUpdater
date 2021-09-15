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

use function property_exists;

/**
 * @property-read Updater $updater
 * @property-read AbstractTranslation[] $translations
 */
abstract class AbstractTranslations
{
    /**
     * @var Updater
     */
    protected $updater;

    /**
     * @var AbstractTranslation[]
     */
    protected $translations = [];

    /**
     * @param Updater $updater
     */
    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
    }

    /**
     * @return Updater
     */
    public function getUpdater(): Updater
    {
        return $this->updater;
    }

    /**
     * @return AbstractTranslations[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     * @param AbstractTranslation $translation
     */
    public function add(string $locale, AbstractTranslation $translation)
    {
        $this->translations[$locale] = $translation;
    }

    /**
     * @param string $locale
     *
     * @return AbstractTranslation|null
     */
    public function get(string $locale)
    {
        return $this->translations[$locale]??null;
    }

    /**
     * Magic method ->$get
     * @param string $name
     *
     * @return mixed|AbstractTranslation
     */
    public function __get(string $name)
    {
        return property_exists($this, $name)
            ? $this->$name
            : $this->get($name);
    }
}
