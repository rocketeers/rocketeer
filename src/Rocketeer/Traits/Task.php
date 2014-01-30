<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits;

use Rocketeer\Bash;

/**
 * An abstract Task with common helpers, from which all Tasks derive
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class Task extends Bash
{
	/**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Whether the Task needs to be run on each stage or globally
	 *
	 * @var boolean
	 */
	public $usesStages = true;

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the basic name of the Task
	 *
	 * @return string
	 */
	public function getSlug()
	{
		$name = get_class($this);
		$name = str_replace('\\', '/', $name);
		$name = basename($name);

		return strtolower($name);
	}

	/**
	 * Get what the Task does
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	abstract public function execute();

	/**
	 * Fire the command
	 *
	 * @return array
	 */
	public function fire()
	{
		$this->fireEvent('before');
		$results = $this->execute();
		$this->fireEvent('after');

		return $results;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// EVENTS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fire an event related to this task
	 *
	 * @param string $event
	 *
	 * @return array|null
	 */
	public function fireEvent($event)
	{
		$event = $this->getQualifiedEvent($event);

		return $this->app['events']->fire($event, array($this));
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
		return 'rocketeer.'.$this->getSlug().'.'.$event;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute another Task by name
	 *
	 * @param  string $task
	 *
	 * @return string The Task's output
	 */
	public function executeTask($task)
	{
		return $this->app['rocketeer.tasks']->buildTask($task)->fire();
	}
}
