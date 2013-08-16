<?php
namespace Rocketeer\Commands;

/**
 * Run the tests on the server and displays the ouput
 */
class DeployTestCommand extends BaseDeployCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run the tests on the server and displays the output';

	/**
	 * The tasks to execute
	 *
	 * @return array
	 */
	public function fire()
	{
		$this->input->setOption('verbose', true);

		return $this->fireTasksQueue('Rocketeer\Tasks\Test');
	}
}
