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

use function array_merge;

/**
 * @property-read string $pot_creation_date
 * @property-read string $creation_date
 * @property-read string $po_revision_date
 * @property-read string $revision_date
 * @property-read string $x_generator
 * @property-read string $generator
 * @property-read string $project_version
 * @property-read string $version
 * @property-read AbstractTranslation $translation
 * @property-read array $data
 * @property-read string[] $defaults
 */
class TranslationMetadata
{
    /**
     * @var array<string, string|array> array for tags
     */
    protected $data;

    /**
     * @var array<string, string>
     */
    protected $defaults = [
        'POT-Creation-Date'  => '',
        'PO-Revision-Date'   => '',
        'Project-Id-Version' => '',
        'X-Generator'        => '',
        'Language'           => '',
        'Language-Team'      => ''
    ];

    /**
     * @var AbstractTranslation
     */
    protected $translation;

    /**
     * @param AbstractTranslation $translation
     * @param array $metadata
     */
    public function __construct(AbstractTranslation $translation, array $metadata = [])
    {
        $this->translation = $translation;
        $this->data        = array_merge($this->defaults, $metadata);
        $this->initialize();
    }

    /**
     * Make default value
     */
    protected function initialize()
    {
        // REVISION
        if (empty($this->data['PO-Revision-Date'])) {
            $this->data['PO-Revision-Date'] = $this->data['revision-date']??'';
        }
        if (empty($this->data['Language'])) {
            $this->data['Language'] = $this->data['language']??'';
        }
        if (empty($this->data['Language'])) {
            $this->data['Language'] = $this->data['locale']??'';
        }
        if (empty($this->data['locale'])) {
            $this->data['locale'] = $this->data['Language']??'';
        }
        // delete language
        unset($this->data['language']);

        if (empty($this->data['Language'])) {
            $this->data['Language'] = $this->data['locale']??'';
        }
        if (empty($this->data['PO-Revision-Date'])) {
            $this->data['PO-Revision-Date'] = $this->data['revision-date'] ?? '';
        }
        if (empty($this->data['PO-Revision-Date'])) {
            $this->data['PO-Revision-Date'] = $this->data['updated-date'] ?? '';
        }
        if (empty($this->data['revision-date'])) {
            $this->data['revision-date'] = $this->data['PO-Revision-Date'];
        }
        if (empty($this->data['updated-date'])) {
            $this->data['updated-date'] = $this->data['PO-Revision-Date'];
        }

        // CREATION
        if (empty($this->data['POT-Creation-Date'])) {
            $this->data['POT-Creation-Date'] = $this->data['created-date']??'';
        }

        if (empty($this->data['created-date'])) {
            $this->data['created-date'] = $this->data['POT-Creation-Date'];
        }

        // VERSION
        if (empty($this->data['Project-Id-Version'])) {
            $this->data['Project-Id-Version'] = $this->data['version']??'';
        }
        if (empty($this->data['version'])) {
            $this->data['version'] = $this->data['Project-Id-Version'];
        }

        // GENERATOR
        if (empty($this->data['X-Generator'])) {
            $this->data['X-Generator'] = $this->data['generator']??'';
        }
        if (empty($this->data['generator'])) {
            $this->data['generator'] = $this->data['X-Generator'];
        }
    }

    /**
     * @return AbstractTranslation
     */
    public function getTranslation(): AbstractTranslation
    {
        return $this->translation;
    }

    /**
     * @return array|string[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string[]
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function get(string $name): string
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        switch (strtolower($name)) {
            case 'language':
                return $this->data['Language']??($this->data['locale']??'');
            case 'locale':
                return $this->data['locale']??($this->data['Language']??'');
            case 'po_revision_date':
            case 'po-revision-date':
                return $this->data['PO-Revision-Date']??(
                    (
                        $this->data['revision']??(
                            $this->data['updated']?? ''
                        )
                    )
                );
            case 'revision-date':
            case 'revision_date':
                return (
                        $this->data['revision']??(
                        $this->data['PO-Revision-Date']??(
                            $this->data['updated']?? ''
                        )
                    )
                );
            case 'update-date':
            case 'update_date':
            case 'updated_date':
            case 'updated-date':
                return (
                    $this->data['updated']??(
                        $this->data['PO-Revision-Date']??(
                            $this->data['revision']?? ''
                        )
                    )
                );
            case 'pot_creation_date':
            case 'pot-creation-date':
                return $this->data['POT-Creation-Date']??($this->data['created'] ?? '');
            case 'creation_date':
            case 'creation-date':
                return $this->data['created']??($this->data['POT-Creation-Date'] ?? '');
            case 'project_version':
            case 'project_id_version':
            case 'project-version':
            case 'project-id-version':
                return $this->data['Project-Id-Version']??($this->data['version']??'');
            case 'version':
                return $this->data['version']??($this->data['Project-Id-Version']??'');
            case 'x_generator':
            case 'x-generator':
                return $this->data['X-Generator']??($this->data['generator']??'');
            case 'generator':
                return $this->data['generator']??($this->data['X-Generator'] ?? '');
        }

        return $this->data[$name] ?? '';
    }

    /**
     * @param string $name
     *
     * @return string|string[]|AbstractTranslation
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'translation':
                return $this->getTranslation();
            case 'data':
                return $this->getData();
            case 'defaults':
                return $this->getDefaults();
        }
        return $this->get($name);
    }
}
