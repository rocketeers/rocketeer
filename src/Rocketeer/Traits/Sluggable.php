<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    /**
     * Get the name of the entity.
     *
     * @return string
     */
    public function getName()
    {
        return isset($this->name) && $this->name ? $this->name : class_basename($this);
    }

    /**
     * Get the basic name of the entity.
     *
     * @return string
     */
    public function getSlug()
    {
        $slug = Str::snake($this->getName(), '-');
        $slug = Str::slug($slug);

        return $slug;
    }
}
