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

use League\Event\Emitter;

/**
 * Assigns tags to listeners to allow
 * batch operations on subsets of them.
 */
class TaggableEmitter extends Emitter
{
    /**
     * The currently active tag.
     *
     * @var string
     */
    protected $tag = '*';

    ////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////// TAGS //////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param string   $tags
     * @param callable $callable
     */
    public function onTag($tags, callable $callable)
    {
        $previous = $this->tag;
        $this->setTag($tags);
        $callable();
        $this->setTag($previous);
    }

    ////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////// OVERRIDES ///////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * {@inheritdoc}
     */
    protected function getSortedListeners($event)
    {
        $listeners = parent::getSortedListeners($event);
        $listeners = array_filter($listeners, function (TaggedListener $listener) {
            return $listener->getTag() === $this->tag || $listener->getTag() === '*';
        });

        return $listeners;
    }

    /**
     * {@inheritdoc}
     */
    protected function ensureListener($listener)
    {
        $listener = parent::ensureListener($listener);
        $listener = new TaggedListener($listener);
        $listener->setTag($this->tag);

        return $listener;
    }
}
