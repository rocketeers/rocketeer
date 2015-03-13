<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Config;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Configuration extends Collection
{
    /**
     * @type array
     */
    protected $rootNodes = [
        'application_name',
        'plugins',
        'logs',
        'default',
        'connections',
        'use_roles',
        'on',
    ];

    /**
     * Get an item from the collection by key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $key = $this->addAliases($key);
        if ($value = Arr::get($this->items, $key, $default)) {
            return $value;
        }

        return value($default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $key = $this->addAliases($key);

        Arr::set($this->items, $key, $value);
    }

    /**
     * Replace the current configuration.
     *
     * @param array $items
     */
    public function replace(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function addAliases($key)
    {
        return Str::startsWith($key, $this->rootNodes) ? 'config.'.$key : $key;
    }
}
