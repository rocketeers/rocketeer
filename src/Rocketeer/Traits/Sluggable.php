<?php
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
