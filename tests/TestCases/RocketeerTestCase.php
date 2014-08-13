<?php
namespace Rocketeer\TestCases;

use Closure;
use Mockery;
use Rocketeer\Services\Storages\LocalStorage;

abstract class RocketeerTestCase extends ContainerTestCase
{
	use RocketeerAssertions;

	/**
	 * The path to the local fake server
	 *
	 * @var string
	 */
	protected $server;

	/**
	 * @type string
	 */
	protected $customConfig;

	/**
	 * The path to the local deployments file
	 *
	 * @var string
	 */
	protected $deploymentsFile;

	/**
	 * A dummy AbstractTask to use for helpers tests
	 *
	 * @var \Rocketeer\Abstracts\AbstractTask
	 */
	protected $task;

	/**
	 * Set up the tests
	 */
	public function setUp()
	{
		parent::setUp();

		// Setup local server
		$this->customConfig    = __DIR__.'/../_meta/.rocketeer';
		$this->server          = __DIR__.'/../_server/foobar';
		$this->deploymentsFile = __DIR__.'/../_meta/deployments.json';

		// Bind new LocalStorage instance
		$this->app->bind('rocketeer.storage.local', function ($app) {
			$folder = dirname($this->deploymentsFile);

			return new LocalStorage($app, 'deployments', $folder);
		});

		// Bind dummy AbstractTask
		$this->task = $this->task('Cleanup');
		$this->recreateVirtualServer();
	}

	/**
	 * Cleanup tests
	 */
	public function tearDown()
	{
		parent::tearDown();

		// Restore superglobals
		$_SERVER['HOME'] = $this->home;
	}

	/**
	 * Recreates the local file server
	 *
	 * @return void
	 */
	protected function recreateVirtualServer()
	{
		// Save superglobals
		$this->home = $_SERVER['HOME'];

		// Recreate deployments file
		$this->app['files']->put($this->deploymentsFile, json_encode(array(
			'foo'                 => 'bar',
			'directory_separator' => '/',
			'is_setup'            => true,
			'webuser'             => array('username' => 'www-data', 'group' => 'www-data'),
			'line_endings'        => "\n",
		)));

		$rootPath = $this->server.'/../../..';

		// Recreate altered local server
		$this->app['files']->deleteDirectory($rootPath.'/storage');
		$this->app['files']->deleteDirectory($this->server.'/logs');
		$folders = array(
			'current',
			'shared',
			'releases',
			'releases/10000000000000',
			'releases/15000000000000',
			'releases/20000000000000'
		);
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
		$this->app['files']->deleteDirectory($this->customConfig);
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
	protected function mockReleases(Closure $expectations)
	{
		return $this->mock('rocketeer.releases', 'ReleasesManager', $expectations);
	}

	/**
	 * Mock a Command
	 *
	 * @param array $options
	 * @param array $expectations
	 */
	protected function mockCommand($options = array(), $expectations = array())
	{
		// Default options
		$options = array_merge(array(
			'pretend'  => false,
			'verbose'  => false,
			'tests'    => false,
			'migrate'  => false,
			'seed'     => false,
			'stage'    => false,
			'parallel' => false,
			'update'   => false,
		), $options);

		$this->app['rocketeer.command'] = $this->getCommand($expectations, $options);
	}

	/**
	 * @param array $state
	 */
	protected function mockState(array $state)
	{
		file_put_contents($this->server.'/state.json', json_encode($state));
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Mock the Composer check
	 *
	 * @param boolean $uses
	 *
	 * @return void
	 */
	protected function usesComposer($uses = true)
	{
		$composer = $this->app['path.base'].DIRECTORY_SEPARATOR.'composer.json';
		$this->mock('files', 'Illuminate\Filesystem\Filesystem', function ($mock) use ($composer, $uses) {
			return $mock->makePartial()->shouldReceive('exists')->with($composer)->andReturn($uses);
		});
	}

	/**
	 * Set Rocketeer in pretend mode
	 *
	 * @param array $options
	 * @param array $expectations
	 */
	protected function pretend($options = array(), $expectations = array())
	{
		$options['pretend'] = true;

		$this->mockCommand($options, $expectations);
	}

	/**
	 * Get a pretend AbstractTask to run bogus commands
	 *
	 * @param string $task
	 * @param array  $options
	 * @param array  $expectations
	 *
	 * @return \Rocketeer\Abstracts\AbstractTask
	 */
	protected function pretendTask($task = 'Deploy', $options = array(), array $expectations = array())
	{
		$this->pretend($options, $expectations);

		return $this->task($task);
	}

	/**
	 * Get AbstractTask instance
	 *
	 * @param string $task
	 * @param array  $options
	 *
	 * @return \Rocketeer\Abstracts\AbstractTask
	 */
	protected function task($task = null, $options = array())
	{
		if ($options) {
			$this->mockCommand($options);
		}

		if (!$task) {
			return $this->task;
		}

		return $this->builder->buildTaskFromClass($task);
	}

	/**
	 * Get TasksQueue instance
	 *
	 * @return \Rocketeer\Services\TasksHandler
	 */
	protected function tasksQueue()
	{
		return $this->app['rocketeer.tasks'];
	}
}
