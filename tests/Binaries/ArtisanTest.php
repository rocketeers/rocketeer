<?php
namespace Rocketeer\Binaries;

use Rocketeer\TestCases\RocketeerTestCase;

class ArtisanTest extends RocketeerTestCase
{
	public function testCanRunMigrations()
	{
		$php     = exec('which php');
		$artisan = new Artisan($this->app);

		$commands = $artisan->migrate();
		$this->assertEquals($php.' artisan migrate', $commands);
	}
}
