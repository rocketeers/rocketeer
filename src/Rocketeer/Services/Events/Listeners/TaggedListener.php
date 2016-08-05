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

use League\Event\EventInterface;
use League\Event\ListenerInterface;

/**
 * A listener that can be assigned
 * a particular tag.
 */
class TaggedListener implements TaggableListenerInterface
{
    use TaggableListenerTrait;

    /**
     * @var ListenerInterface
     */
    protected $listener;

    /**
     * @param ListenerInterface $listener
     */
    public function __construct(ListenerInterface $listener)
    {
        $this->listener = $listener;
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
