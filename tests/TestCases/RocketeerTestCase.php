<?php
namespace Rocketeer\TestCases;

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
			'current_release'     => array('production' => 20000000000000),
			'directory_separator' => '/',
			'is_setup'            => true,
			'webuser'             => array('username' => 'www-data','group' => 'www-data'),
			'line_endings'        => "\n",
		)));

		$rootPath = $this->server.'/../../..';

		// Recreate altered local server
		$this->app['files']->deleteDirectory($rootPath.'/storage');
		$folders = array('current', 'shared', 'releases', 'releases/10000000000000', 'releases/15000000000000', 'releases/20000000000000');
		foreach ($folders as $folder) {
			$folder = $this->server.'/'.$folder;

			$this->app['files']->deleteDirectory($folder);
			$this->app['files']->delete($folder);
			$this->app['files']->makeDirectory($folder, 0777, true);
			file_put_contents($folder.'/.gitkeep', '');
		}
		file_put_contents($this->server.'/state.json', json_encode(array(
			'10000000000000' => true,
			'15000000000000' => false,
			'20000000000000' => true,
		)));

		// Delete rocketeer config
		$binary = $rootPath.'/.rocketeer';
		$this->app['files']->deleteDirectory($binary);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// ASSERTIONS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Assert a task has a particular output
	 *
	 * @param string  $task
	 * @param string  $output
	 * @param Mockery $command
	 *
	 * @return Assertion
	 */
	protected function assertTaskOutput($task, $output, $command = null)
	{
		return $this->assertContains($output, $this->task($task, $command)->execute());
	}

	/**
	 * Assert a task's history matches an array
	 *
	 * @param string|Task  $task
	 * @param array        $history
	 * @param array        $options
	 *
	 * @return string
	 */
	protected function assertTaskHistory($task, array $history, array $options = array())
	{
		// Create task if needed
		if (is_string($task)) {
			$task = $this->pretendTask($task, $options);
		}

		// Execute task
		$history = $this->replaceHistoryPlaceholders($history);
		$results = $task->execute();

		// Check equality
		$this->assertEquals($history, $task->getHistory());

		return $results;
	}

	/**
	 * Replace placeholders in an history
	 *
	 * @param array $history
	 *
	 * @return array
	 */
	protected function replaceHistoryPlaceholders($history, $release = null)
	{
		$release = $release ?: date('YmdHis');

		foreach ($history as $key => $entries) {
			if (is_array($entries)) {
				$history[$key] = $this->replaceHistoryPlaceholders($entries, $release);
				continue;
			}

			$history[$key] = strtr($entries, array(
				'{php}'     => exec('which php'),
				'{phpunit}' => exec('which phpunit'),
				'{server}'  => $this->server,
				'{release}' => $release,
			));
		}

		return $history;
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
	protected function pretendTask($task = 'Deploy', $options = array(), array $expectations = array())
	{
		// Default options
		$options = array_merge(array(
			'pretend' => true,
			'verbose' => false,
		), $options);

		return $this->task($task, $this->getCommand($expectations, $options));
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
