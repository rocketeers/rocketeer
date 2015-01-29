<?php
namespace Rocketeer\Traits\Properties;

/**
 * A class that can fire events
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait HasEvents
{
    /**
     * Fire an event related to this task
     *
     * @param string $event
     *
     * @return boolean
     */
    public function fireEvent($event)
    {
        $event     = $this->getQualifiedEvent($event);
        $listeners = $this->events->getListeners($event);

        // Fire the event
        $result = $this->explainer->displayBelow(function () use ($listeners) {
            foreach ($listeners as $listener) {
                $response = call_user_func_array($listener, [$this]);
                if ($response === false) {
                    return false;
                }
            }

            return true;
        });

        // If the event returned a strict false, halt the task
        if ($result === false && method_exists($this, 'halt')) {
            $this->halt();
        }

        return $result !== false;
    }

    /**
     * Get the fully qualified event name
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
