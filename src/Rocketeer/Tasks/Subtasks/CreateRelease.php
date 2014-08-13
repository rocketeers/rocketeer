<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;

class CreateRelease extends AbstractTask
{
	/**
	 * A description of what the task does
	 *
	 * @var string
	 */
	protected $description = 'Creates a new release on the server';

	/**
	 * Run the task
	 *
	 * @return string
	 */
	public function execute()
	{
		return $this->getStrategy('Deploy')->deploy();
	}
}
