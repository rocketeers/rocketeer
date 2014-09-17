<?php
namespace Rocketeer\Services\Storages;

use Rocketeer\TestCases\RocketeerTestCase;

class ServerStorageTest extends RocketeerTestCase
{
	public function testCanDestroyRemoteFile()
	{
		$server = new ServerStorage($this->app, 'test');
		$file   = $server->getFilepath();
		$server->destroy();

		$this->assertFileNotExists($file);
	}
}
