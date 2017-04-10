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

namespace Rocketeer\Traits\Properties;

use League\Event\EventInterface;
use Rocketeer\Interfaces\IdentifierInterface;

/**
 * A class that can fire events.
 */
trait HasEventsTrait
{
    /**
     * The event this task is answering to.
     *
     * @var EventInterface
     */
    protected $event;

    /**
     * Whether the task was halted mid-course.
     *
     * @var bool
     */
    protected $halted = false;

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
     * @param IdentifierInterface $listener
     *
     * @return bool
     */
    public function isListener($listener)
    {
        return $listener->getIdentifier() === $this->getIdentifier();
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HALTING ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Cancel the task.
     *
     * @param string|null $errors Potential errors to display
     *
     * @return bool
     */
    public function halt($errors = null)
    {
        // Display errors
        if ($errors) {
            $this->explainer->error($errors);
        }

        $this->fireEvent('halt');
        $this->halted = true;

        if ($this->event) {
            $this->getEvent()->stopPropagation();
        }

        return false;
    }

    /**
     * Whether the task was halted mid-course.
     *
     * @return bool
     */
    public function wasHalted()
    {
        return $this->halted === true;
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// EVENTS FIRING ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Run a callable and fire a before/after event around it.
     *
     * @param callable $callable
     *
     * @return bool
     */
    public function runWithBeforeAfterEvents(callable $callable)
    {
        $results = false;

        // Fire the task if the before event passes
        if ($this->fireEvent('before')) {
            $results = $this->timer->time($this, $callable);
            if ($results && !$this->wasHalted()) {
                $this->fireEvent('after');
            }
        }

        return $results;
    }

    /**
     * Fire an event related to this task.
     *
     * @param string $event
     *
     * @return bool
     */
    public function fireEvent($event)
    {
        $handle = $this->getQualifiedEvent($event);

        // Fire the event
        /** @var \League\Event\EventInterface $event */
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
