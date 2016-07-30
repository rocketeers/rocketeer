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

namespace Rocketeer\Tasks;

/**
 * A task that wraps around a closure and execute it.
 */
class Closure extends AbstractTask
{
    /**
     * A callable to execute at runtime.
     *
     * @var callable
     */
    protected $closure;

    /**
     * A string task to execute in the Closure.
     *
     * @var string|string[]|callable
     */
    protected $stringTask;

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// FLUENT INTERFACE //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Change what the task does.
     *
     * @param string|array|callable $task
     *
     * @return self
     */
    public function does($task)
    {
        // Store the original task before transformation
        $this->setStringTask($task);

        // Wrap string tasks
        if (is_string($task) || is_array($task)) {
            $task = $this->builder->wrapStringTasks($task);
        }

        $this->setClosure($task);

        return $this;
    }

    /**
     * Fluent alias for setDescription.
     *
     * @param string $description
     *
     * @return self
     */
    public function description($description)
    {
        $this->setDescription($description);

        return $this;
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////// GETTERS AND SETTERS /////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the name of the task.
     *
     * @return string
     */
    public function getName()
    {
        return parent::getName() ?: 'Arbitrary task';
    }

    /**
     * Get what the task does.
     *
     * @return string
     */
    public function getDescription()
    {
        $flattened = (array) $this->getStringTask();
        $flattened = implode('/', $flattened);

        return parent::getDescription() ?: $flattened;
    }

    /**
     * Create a task from a Closure.
     *
     * @param callable $closure
     */
    public function setClosure(callable $closure)
    {
        $this->closure = $closure;
    }

    /**
     * Get the task's callable.
     *
     * @return callable
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Get the string task that was assigned.
     *
     * @return string
     */
    public function getStringTask()
    {
        return $this->stringTask;
    }

    /**
     * Set the string task.
     *
     * @param string|string[]|callable $task
     */
    public function setStringTask($task)
    {
        $this->stringTask = $task;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $closure = $this->closure->bindTo($this);

        return $closure($this);
    }
}
