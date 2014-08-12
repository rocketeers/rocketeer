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
use Symfony\Component\Console\Helper\Table;

/**
 * An abstract AbstractTask with common helpers, from which all Tasks derive
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractTask extends Bash
{
	/**
	 * The name of the task
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * A description of what the task does
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

	////////////////////////////////////////////////////////////////////
	////////////////////////////// REFLECTION //////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the name of the task
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name ?: class_basename($this);
	}

	/**
	 * Get the basic name of the task
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
	 * Get what the task does
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Change the task's name
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = ucfirst($name) ?: $this->name;
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
	 * Run the task
	 *
	 * @return string
	 */
	abstract public function execute();

	/**
	 * Fire the command
	 *
	 * @return string|false
	 */
	public function fire()
	{
		// Print status
		$this->displayStatus();

		// Fire the task if the before event passes
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
	 * @param string|null $errors Potential errors to display
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
	 * Whether the task was halted mid-course
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
	 * @return boolean
	 */
	public function fireEvent($event)
	{
		// Fire the event
		$event  = $this->getQualifiedEvent($event);
		$result = $this->events->fire($event, [$this]);

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
	 */
	protected function displayReleases()
	{
		$releases = $this->releasesManager->getValidationFile();
		$this->command->comment('Here are the available releases :');

		$output = $this->command->getOutput();
		if (!$output) {
			return;
		}

		// Build table
		$table = new Table($output);
		$table->setHeaders(['#', 'Path', 'Deployed at', 'Status']);

		// Append the rows
		$key  = 0;
		$rows = [];
		foreach ($releases as $name => $state) {
			$date  = DateTime::createFromFormat('YmdHis', $name);
			$date  = $date->format('Y-m-d H:i:s');
			$icon  = $state ? '✓' : '✘';
			$color = $state ? 'green' : 'red';

			// Add color to row
			$row    = [$key, $name, $date, $icon];
			$row[3] = sprintf('<fg=%s>%s</fg=%s>', $color, $row[3], $color);
			$rows[] = $row;
			$key++;
		}

		$table->addRows($rows);
		$table->render();

		return $rows;
	}

	/**
	 * Execute another AbstractTask by name
	 *
	 * @param  string $task
	 *
	 * @return string|false
	 */
	public function executeTask($task)
	{
		return $this->builder->buildTaskFromClass($task)->fire();
	}

	/**
	 * Display what the command is and does
	 */
	protected function displayStatus()
	{
		if (!$this->command) {
			return;
		}

		$name        = $this->getName();
		$description = $this->getDescription();
		$comment     = sprintf('-- Running: <info>%s</info>', $name);
		if ($description) {
			$comment .= ' <comment>('.$description.')</comment>';
		}

		$this->command->line($comment);
	}
}
