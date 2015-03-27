<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

use DateTime;
use Illuminate\Support\Str;
use Rocketeer\Bash;
use Rocketeer\Traits\StepsRunner;

/**
 * An abstract AbstractTask with common helpers, from which all Tasks derive.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractTask extends Bash
{
    use StepsRunner;

    /**
     * The name of the task.
     *
     * @type string
     */
    protected $name;

    /**
     * A description of what the task does.
     *
     * @type string
     */
    protected $description;

    /**
     * The event this task is answering to.
     *
     * @type string
     */
    protected $event;

    /**
     * Whether the task was halted mid-course.
     *
     * @type bool
     */
    protected $halted = false;

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// REFLECTION //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get the name of the task.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?: class_basename($this);
    }

    /**
     * Get the basic name of the task.
     *
     * @return string
     */
    public function getSlug()
    {
        $slug = Str::snake($this->getName(), '-');
        $slug = Str::slug($slug);

        return $slug;
    }

    /**
     * Get what the task does.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Change the task's name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = ucfirst($name) ?: $this->name;
    }

    /**
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description ?: $this->description;
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// EXECUTION ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Run the task.
     *
     * @return string
     */
    abstract public function execute();

    /**
     * Fire the command.
     *
     * @return bool
     */
    public function fire()
    {
        // Print status
        $results = false;
        $this->displayStatus();

        // Fire the task if the before event passes
        if ($this->fireEvent('before')) {
            $this->timer->time($this, function () use (&$results) {
                $results = $this->execute();
            });
            $this->fireEvent('after');
        }

        return $results;
    }

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
            $this->command->error($errors);
        }

        $this->fireEvent('halt');
        $this->halted = true;

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

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// EVENTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Fire an event related to this task.
     *
     * @param string $event
     *
     * @return bool
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
        if ($result === false) {
            $this->halt();
        }

        return $result !== false;
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
        return 'rocketeer.'.$this->getSlug().'.'.$event;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Display a list of releases and their status.
     *
     * @codeCoverageIgnore
     */
    protected function displayReleases()
    {
        if (!$this->command) {
            return;
        }

        $key      = 0;
        $rows     = [];
        $releases = $this->releasesManager->getValidationFile();

        // Append the rows
        foreach ($releases as $name => $state) {
            $icon  = $state ? '✓' : '✘';
            $color = $state ? 'green' : 'red';
            $date  = DateTime::createFromFormat('YmdHis', $name)->format('Y-m-d H:i:s');
            $date  = sprintf('<fg=%s>%s</fg=%s>', $color, $date, $color);

            // Add color to row
            $rows[] = [$key, $name, $date, $icon];
            $key++;
        }

        // Render table
        $this->command->comment('Here are the available releases :');
        $this->command->table(
            ['#', 'Path', 'Deployed at', 'Status'],
            $rows
        );

        return $rows;
    }

    /**
     * Display what the command is and does.
     */
    protected function displayStatus()
    {
        $name        = $this->getName();
        $description = $this->getDescription();
        $time        = $this->timer->getTaskTime($this);

        $this->explainer->display($name, $description, $this->event, $time);
    }
}
