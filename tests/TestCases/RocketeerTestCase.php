<?php
namespace Rocketeer\Tests\TestCases;

include __DIR__.'/../../vendor/autoload.php';

use Rocketeer\Server;

abstract class RocketeerTestCase extends ContainerTestCase
{
	/**
	 * The path to the local fake server
	 *
	 * @var string
	 */
	protected $server;

	/**
	 * The path to the local deployments file
	 *
	 * @var string
	 */
	protected $deploymentsFile;

	/**
	 * The current path to PHP
	 *
	 * @var string
	 */
	protected $php;

	/**
	 * A dummy Task to use for helpers tests
	 *
	 * @var Task
	 */
	protected $task;

	/**
	 * The test repository
	 *
	 * @var string
	 */
	protected $repository = 'Anahkiasen/html-object.git';

	/**
	 * Set up the tests
	 */
	public function setUp()
	{
		parent::setUp();

		// Setup local server
		$this->server          = __DIR__.'/../_server/foobar';
		$this->deploymentsFile = __DIR__.'/../_meta/deployments.json';
		$this->php             = exec('which php');

		// Bind new Server instance
		$meta = dirname($this->deploymentsFile);
		$this->app->bind('rocketeer.server', function ($app) use ($meta) {
			return new Server($app, 'deployments', $meta);
		});

		// Bind dummy Task
		$this->task = $this->task('Cleanup');
		$this->recreateVirtualServer();
	}

	/**
	 * Recreates the local file server
	 *
	 * @return void
	 */
	protected function recreateVirtualServer()
	{
		// Recreate deployments file
		$this->app['files']->put($this->deploymentsFile, json_encode(array(
			'foo'                 => 'bar',
			'current_release'     => 20000000000000,
			'directory_separator' => '/',
			'is_setup'            => true,
			'webuser'             => array('username' => 'www-data','group' => 'www-data'),
			'line_endings'        => "\n",
		)));

		$rootPath = $this->server.'/../../..';

		// Recreate altered local server
		$this->app['files']->deleteDirectory($rootPath.'/storage');
		$folders = array('current', 'shared', 'releases', 'releases/10000000000000', 'releases/20000000000000');
		foreach ($folders as $folder) {
			$folder = $this->server.'/'.$folder;

			$this->app['files']->deleteDirectory($folder);
			$this->app['files']->delete($folder);
			$this->app['files']->makeDirectory($folder, 0777, true);
			file_put_contents($folder.'/.gitkeep', '');
		}

		// Delete rocketeer config
		$binary = $rootPath.'/rocketeer';
		$this->app['files']->deleteDirectory($binary);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// MOCKERIES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Mock the ReleasesManager
	 *
	 * @param Closure $expectations
	 *
	 * @return Mockery
	 */
	protected function mockReleases($expectations)
	{
		return $this->mock('rocketeer.releases', 'ReleasesManager', $expectations);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a pretend Task to run bogus commands
	 *
	 * @return Task
	 */
	protected function pretendTask($task = 'Deploy', $options = array())
	{
		// Default options
		$default = array('pretend' => true, 'verbose' => false);
		$options = array_merge($default, $options);

		// Create command
		$command = clone $this->getCommand();
		foreach ($options as $name => $value) {
			$command->shouldReceive('option')->with($name)->andReturn($value);
		}

		// Bind it to Task
		$task = $this->task($task);
		$this->app['rocketeer.command'] = $command;

		return $task;
	}

	/**
	 * Get Task instance
	 *
	 * @param  string $task
	 *
	 * @return Task
	 */
	protected function task($task = null, $command = null)
	{
		if ($command) {
			$this->app['rocketeer.command'] = $command;
		}

		if (!$task) {
			return $this->task;
		}

		return $this->tasksQueue()->buildTask('Rocketeer\Tasks\\'.$task);
	}

	/**
	 * Get TasksQueue instance
	 *
	 * @return TasksQueue
	 */
	protected function tasksQueue()
	{
		return $this->app['rocketeer.tasks'];
	}
}
