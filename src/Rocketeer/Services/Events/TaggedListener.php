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

namespace Rocketeer\Services\Events;

use League\Event\EventInterface;
use League\Event\ListenerInterface;

/**
 * A listener that can be assigned
 * a particular tag.
 */
class TaggedListener implements ListenerInterface
{
    /**
     * @var ListenerInterface
     */
    protected $listener;

    /**
     * @var string
     */
    protected $tag;

    /**
     * @param ListenerInterface $listener
     */
    public function __construct(ListenerInterface $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EventInterface $event)
    {
        return $this->listener->handle($event);
    }

    /**
     * {@inheritdoc}
     */
    public function isListener($listener)
    {
        return $this->listener->isListener($listener);
    }
}
