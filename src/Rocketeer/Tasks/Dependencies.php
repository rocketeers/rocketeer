<?php
namespace Rocketeer\Tasks;

use Rocketeer\Abstracts\AbstractTask;

class Dependencies extends AbstractTask
{
	/**
	 * A description of what the task does
	 *
	 * @var string
	 */
	protected $description = 'Installs or update the dependencies on server';

	/**
	 * Run the task
	 *
	 * @return boolean
	 */
	public function execute()
	{
		$method = $this->getOption('update') ? 'update' : 'install';

		return $this->getStrategy('Dependencies')->$method();
	}
}
