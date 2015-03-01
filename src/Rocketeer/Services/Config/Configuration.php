<?php
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
     * @param  string $key
     * @param  mixed  $default
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
     * @param string $key
     *
     * @return string
     */
    private function addAliases($key)
    {
        return Str::startsWith($key, $this->rootNodes) ? 'config.'.$key : $key;
    }
}
