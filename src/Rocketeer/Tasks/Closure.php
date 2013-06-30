<?php
namespace Rocketeer\Tasks;

use Rocketeer\Tasks\Abstracts\Task;
use Closure as AnonymousFunction;

/**
 * A task formatted as a Closure
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
