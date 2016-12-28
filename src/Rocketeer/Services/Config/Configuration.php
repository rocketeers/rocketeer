<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Config;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * An Arrayable containing the current configuration and easing
 * the access to the main configuration nodes.
 */
class Configuration extends Collection implements ConfigurationInterface
{
    /**
     * @var array
     */
    protected $rootNodes = [
        'application_name',
        'logs',
        'logs_path',
        'default',
        'connections',
        'on',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getArrayableItems($items)
    {
        // Replace environment variables
        $items = parent::getArrayableItems($items);
        array_walk_recursive($items, function (&$value) {
            if (is_string($value) && mb_strpos($value, '%%') === 0) {
                $value = getenv(mb_substr($value, 2, -2));
            }
        });

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $key = $this->prefixKey($key);
        if ($value = Arr::get($this->items, $key, $default)) {
            return $value;
        }

        return value($default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $key = $this->prefixKey($key);

        Arr::set($this->items, $key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $items)
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function prefixKey($key)
    {
        $first = explode('.', $key)[0];
        $isRootNode = in_array($first, $this->rootNodes, true);

        return $isRootNode ? 'config.'.$key : $key;
    }
}
