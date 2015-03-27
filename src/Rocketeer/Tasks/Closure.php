<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Tasks;

use Closure as AnonymousFunction;
use Rocketeer\Abstracts\AbstractTask;

/**
 * a task that wraps around a closure and execute it.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Closure extends AbstractTask
{
    /**
     * A Closure to execute at runtime.
     *
     * @type AnonymousFunction
     */
    protected $closure;

    /**
     * A string task to execute in the Closure.
     *
     * @type string
     */
    protected $stringTask;

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// FLUENT INTERFACE //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Change what the task does.
     *
     * @param string|array|\Closure $task
     *
     * @return self
     */
    public function does($task)
    {
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
     * @param AnonymousFunction $closure
     */
    public function setClosure(AnonymousFunction $closure)
    {
        $this->closure = $closure;
    }

    /**
     * Get the task's Closure.
     *
     * @return AnonymousFunction
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
     * @param string $task
     */
    public function setStringTask($task)
    {
        $this->stringTask = $task;
    }

    /**
     * Run the task.
     */
    public function execute()
    {
        $closure = $this->closure;

        return $closure($this);
    }
}
