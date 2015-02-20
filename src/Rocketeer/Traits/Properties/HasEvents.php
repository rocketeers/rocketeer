<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Traits\Properties;

use League\Event\EventInterface;

/**
 * A class that can fire events.
 *
 * @mixin \Rocketeer\Abstracts\AbstractTask
 * @mixin \Rocketeer\Abstracts\Commands\AbstractCommand
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait HasEvents
{
    /**
     * The event this task is answering to.
     *
     * @type EventInterface
     */
    protected $event;

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// LISTENER //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param EventInterface $event
     */
    public function setEvent(EventInterface $event)
    {
        $this->event = $event;
    }

    /**
     * @return EventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        $this->setEvent($event);
        $this->fire();
    }

    /**
     * @param $listener
     *
     * @return bool
     */
    public function isListener($listener)
    {
        return $listener->getIdentifier() === $this->getIdentifier();
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// EVENTS FIRING ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Run a callable and fire a before/after event arround it.
     *
     * @param callable $callable
     *
     * @return boolean
     */
    public function runWithBeforeAfterEvents(callable $callable)
    {
        $results = false;

        // Fire the task if the before event passes
        if ($this->fireEvent('before')) {
            $results = $this->timer->time($this, $callable);
            $this->fireEvent('after');
        }

        return $results;
    }

    /**
     * Fire an event related to this task.
     *
     * @param string $event
     *
     * @return boolean
     */
    public function fireEvent($event)
    {
        $handle = $this->getQualifiedEvent($event);

        // Fire the event
        /** @type \League\Event\EventInterface $event */
        $event = $this->explainer->displayBelow(function () use ($handle) {
            return $this->events->emit($handle, [$this]);
        });

        // If the event returned a strict false, halt the task
        $wasHalted = $event && $event->isPropagationStopped();
        if ($wasHalted && $event !== 'halt' && method_exists($this, 'halt')) {
            $this->halt();
        }

        return !$wasHalted;
    }

    /**
     * Get the fully qualified event name.
     *
     * @param string $event
     *
     * @return string
     */
    public function getQualifiedEvent($event)
    {
        return $this->tasks->getEventHandle($this, $event);
    }
}
