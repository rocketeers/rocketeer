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

namespace Rocketeer\Services\Events\Listeners;

use League\Event\ListenerInterface;

/**
 * Interface for listeners that can be assigned a tag.
 */
interface TaggableListenerInterface extends ListenerInterface
{
    /**
     * @param string $tag
     */
    public function setTag($tag);

    /**
     * @return string
     */
    public function getTag();
}
