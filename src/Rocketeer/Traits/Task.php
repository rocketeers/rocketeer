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

use DateTime;
use Rocketeer\Bash;

/**
 * An abstract Task with common helpers, from which all Tasks derive
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class Task extends Bash
{
	/**
	 * The name of the task
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * A description of what the Task does
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Whether the task was halted mid-course
	 *
	 * @var boolean
	 */
	protected $halted = false;

	/**
	 * Whether the Task needs to be run on each stage or globally
	 *
	 * @var boolean
	 */
	public $usesStages = true;

	////////////////////////////////////////////////////////////////////
	////////////////////////////// REFLECTION //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the name of the Task
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name ?: class_basename($this);
	}

	/**
	 * Change the Task's name
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Get the basic name of the Task
	 *
	 * @return string
	 */
	public function getSlug()
	{
		return strtolower($this->getName());
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

	////////////////////////////////////////////////////////////////////
	////////////////////////////// EXECUTION ///////////////////////////
	////////////////////////////////////////////////////////////////////

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
		// Fire the Task if the before event passes
		if ($this->fireEvent('before')) {
			$results = $this->execute();
			$this->fireEvent('after');

			return $results;
		}

		return false;
	}

	/**
	 * Cancel the task
	 *
	 * @param string  $errors Potential errors to display
	 *
	 * @return boolean
	 */
	public function halt($errors = null)
	{
		// Display errors
		if ($errors) {
			$this->command->error($errors);
		}

		$this->halted = true;

		return false;
	}

	/**
	 * Whether the Task was halted mid-course
	 *
	 * @return boolean
	 */
	public function wasHalted()
	{
		return $this->halted === true;
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
		// Fire the event
		$event  = $this->getQualifiedEvent($event);
		$result = $this->app['events']->fire($event, array($this), true);

		// If the event returned a strict false, halt the task
		if ($result === false) {
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
		return 'rocketeer.'.$this->getSlug().'.'.$event;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Display a list of releases and their status
	 *
	 * @return void
	 */
	protected function displayReleases()
	{
		$releases = $this->releasesManager->getValidationFile();
		$this->command->comment('Here are the available releases :');

		$key = 0;
		foreach ($releases as $name => $state) {
			$name   = DateTime::createFromFormat('YmdHis', $name);
			$name   = $name->format('Y-m-d H:i:s');
			$method = $state ? 'info' : 'error';
			$state  = $state ? '✓' : '✘';

			$key++;
			$this->command->$method(sprintf('[%d] %s %s', $key, $name, $state));
		}
	}

	/**
	 * Execute another Task by name
	 *
	 * @param  string $task
	 *
	 * @return string The Task's output
	 */
	public function executeTask($task)
	{
		return $this->app['rocketeer.tasks']->buildTaskFromClass($task)->fire();
	}
}
