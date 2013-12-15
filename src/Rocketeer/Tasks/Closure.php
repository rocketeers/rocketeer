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
use Rocketeer\Traits\Task;

/**
 * A Task that wraps around a closure and execute it
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Closure extends Task
{
	/**
	 * A Closure to execute at runtime
	 *
	 * @var Closure
	 */
	protected $closure;

	/**
	 * A string task to execute in the Closure
	 *
	 * @var string
	 */
	protected $stringTask;

	/**
	 * Create a Task from a Closure
	 *
	 * @param  AnonymousFunction $closure
	 */
	public function setClosure(AnonymousFunction $closure)
	{
		$this->closure = $closure;
	}

	/**
	 * Get the Task's Closure
	 *
	 * @return Closure
	 */
	public function getClosure()
	{
		return $this->closure;
	}

	/**
	 * Get the string task that was assigned
	 *
	 * @return string
	 */
	public function getStringTask()
	{
		return $this->stringTask;
	}

	/**
	 * Set the string task
	 *
	 * @param string $task
	 */
	public function setStringTask($task)
	{
		$this->stringTask = $task;
	}

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$closure = $this->closure;

		return $closure($this);
	}
}
