<?php
namespace Rocketeer\TestCases;

use Rocketeer\Services\Storages\LocalStorage;
use Rocketeer\TestCases\Modules\RocketeerAssertions;
use Rocketeer\TestCases\Modules\RocketeerMockeries;

abstract class RocketeerTestCase extends ContainerTestCase
{
	use RocketeerAssertions;
	use RocketeerMockeries;

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
	 * Cache of the paths to binaries
	 *
	 * @type array
	 */
	protected $binaries = [];

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

		// Cache paths
		$this->binaries = array(
			'php'      => exec('which php') ?: 'php',
			'bundle'   => exec('which bundle') ?: 'bundle',
			'phpunit'  => exec('which phpunit') ?: 'phpunit',
			'composer' => exec('which composer') ?: 'composer',
		);
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
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

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
