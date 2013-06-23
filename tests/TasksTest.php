<?php
class TasksTest extends RocketeerTests
{

	public function tearDown()
	{
		// Recreate altered local server
		$folders = array('current', 'releases/1000000000', 'releases/2000000000');
		foreach ($folders as $folder) {
			$folder = $this->server.'/'.$folder;
			if (!file_exists($folder)) {
				$this->app['files']->makeDirectory($folder, 0777, true);
				file_put_contents($folder.'/.gitkeep', '');
			}
		}
	}

	public function testCanCleanupServer()
	{
		$cleanup = $this->tasksQueue()->buildTask('Rocketeer\Tasks\Cleanup');
		$cleanup->execute();

		$this->assertFalse(file_exists($this->server.'/releases/1000000000'));
	}

}