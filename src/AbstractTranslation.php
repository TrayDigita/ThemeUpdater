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

use function call_user_func;

/**
 * @property-read TranslationMetadata $metadata
 * @property-read AbstractTranslations $translations
 * @mixin TranslationMetadata
 */
abstract class AbstractTranslation
{
    const DATE_FORMAT = 'Y-m-d H:i:sO';

    /**
     * @var TranslationMetadata
     */
    protected $metadata;

    /**
     * @var AbstractTranslations
     */
    protected $translations;

    /**
     * @param AbstractTranslations $translations
     */
    public function __construct(AbstractTranslations $translations)
    {
        $this->translations = $translations;
        $this->metadata = new TranslationMetadata($this);
    }

    /**
     * @return AbstractTranslations
     */
    public function getTranslations(): AbstractTranslations
    {
        return $this->translations;
    }

    /**
     * @return TranslationMetadata
     */
    public function getMetadata() : TranslationMetadata
    {
        return $this->metadata;
    }

    /**
     * @param string $name
     *
     * @return array[]|string|string[]|AbstractTranslation|TranslationMetadata
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'translation':
                return $this;
            case 'translations':
                return $this->getTranslation();
            case 'metadata':
                return $this->getMetadata();
        }

        return $this->getMetadata()->$name;
    }
    public function __call($name, $arguments)
    {
        return call_user_func([$this->getMetadata(), $name], ...$arguments);
    }
}
