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
/** @noinspection PhpUnused */
declare(strict_types=1);

namespace TrayDigita\ThemeUpdater;

use Exception;
use Psr\Log\LoggerInterface;
use Throwable;
use WP_Error;
use function get_class;
use function is_numeric;
use function is_string;
use function property_exists;
use function str_replace;
use function strstr;
use function strtolower;

/**
 * Abstract adapter
 * please make magic method __get($name) support public accessible belo property
 *
 * @property-read Updater $updater
 * @property-read string $name
 * @property-read string $version
 * @property-read string $description
 */
abstract class AbstractAdapter
{
    /**
     * @var string
     * @private
     */
    private $id;

    /**
     * @var int|numeric priority of update
     */
    protected $priority = 10;

    /**
     * @var bool
     */
    private $processed = false;

    /**
     * @var string adapter name
     */
    protected $name = '';

    /**
     * @var string adapter version
     */
    protected $version = '';

    /**
     * @var string adapter description
     */
    protected $description = '';

    /**
     * @var WP_Error|null|Result|Throwable
     */
    private $last_result = null;

    /**
     * @var Updater
     */
    protected $updater;

    /**
     * @var ResultTranslations
     */
    protected $result_translations;

    /**
     * @param Updater $updater
     * @param string $id
     */
    final public function __construct(
        Updater $updater,
        string $id = ''
    ) {
        // don't allow new construct for safe usage
        if ($this->id) {
            return;
        }
        $this->updater = $updater;
        // class name
        $className = get_class($this);
        // if id is empty
        $this->id = trim($id)?: strtolower(str_replace('\\', '_', $className));
        // if priority is not a numeric, fallback default
        ! is_numeric($this->priority) && $this->priority = 10;
        // set max for -999
        $this->priority < -9999 && $this->priority = -999;
        // make default value when it was not valid name
        if (!$this->name || ! is_string($this->name)) {
            $this->name = strstr($className, '\\', true);
        }
        // do construct
        $this->onConstruct();
    }

    /**
     * @return string
     */
    final public function getId(): string
    {
        return $this->id;
    }

    /**
     * method to call the checking process, allow reprocessing data
     *
     * @return Result|WP_Error|null
     * @see AbstractAdapter::onProcess()
     */
    final public function process()
    {
        $this->processed = true;
        try {
            $this->last_result = $this->onProcess();
        } catch (Throwable $e) {
            $this->last_result = $e;
        }
        return $this->last_result;
    }

    /**
     * Override to use force update
     *
     * @return Throwable|Result|WP_Error|null
     */
    public function getLastResult()
    {
        return $this->last_result;
    }

    /**
     * @return bool
     */
    final public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * Method process called when process method on call
     *
     * @return Result|WP_Error|null null if truly invalid or could not get data
     * @see AbstractAdapter::process()
     * @throws Exception or throw exception that script can not handle
     */
    abstract protected function onProcess();

    /**
     * Method process called when object on construct
     * @see AbstractAdapter::__construct()
     */
    protected function onConstruct()
    {
        // pass please override if needed
    }

    /**
     * @return int|numeric
     */
    public function getPriority()
    {
        return $this->priority;
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
    public function getName(): string
    {
        return $this->name;
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->updater->getLogger();
    }

    /**
     * Magic method property
     *
     * @param string $name
     *
     * @return string|null
     */
    public function __get(string $name)
    {
        return property_exists($this, $name)
            ? $this->name
            : null;
    }
}
