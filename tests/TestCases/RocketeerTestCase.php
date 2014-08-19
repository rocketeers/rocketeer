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
		$this->server          = __DIR__.'/../_server/foobar';
		$this->customConfig    = $this->server.'/../.rocketeer';
		$this->deploymentsFile = $this->server.'/deployments.json';

		// Bind dummy AbstractTask
		$this->task = $this->task('Cleanup');
		$this->recreateVirtualServer();

		// Bind new LocalStorage instance
		$this->app->bind('rocketeer.storage.local', function ($app) {
			$folder = dirname($this->deploymentsFile);

			return new LocalStorage($app, 'deployments', $folder);
		});
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

		// Cleanup files created by tests
		$cleanup = array(
			realpath(__DIR__.'/../../.rocketeer'),
			realpath(__DIR__.'/../.rocketeer'),
			realpath($this->server),
			realpath($this->customConfig),
		);
		array_map([$this->files, 'deleteDirectory'], $cleanup);
		if (is_link($this->server.'/current')) {
			unlink($this->server.'/current');
		}

		// Recreate altered local server
		$this->files->copyDirectory($this->server.'-stub', $this->server);
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
		$composer = $this->server.'/current/composer.json';
		if ($uses) {
			$this->files->put($composer, '{}');
		} else {
			$this->files->delete($composer);
		}
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
}
