<?php
namespace Rocketeer\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Deploy the website
 */
class DeployDeployCommand extends BaseDeployCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:deploy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Deploy the website.';

	/**
	 * Execute the tasks
	 *
	 * @return array
	 */
	public function fire()
	{
		return $this->fireTasksQueue(array(
			'Rocketeer\Tasks\Deploy',
			'Rocketeer\Tasks\Cleanup',
		));
	}

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return array(
      array('phpunit', 'p', InputOption::VALUE_NONE, 'Runs the tests on deploy')
    );
  }

}
