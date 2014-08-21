<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class BinariesTest extends RocketeerTestCase
{
	public function testCanSetCustomPathsForBinaries()
	{
		$this->app['config'] = $this->getConfig(array('rocketeer::paths.composer' => 'foobar'));

		$this->assertEquals('foobar', $this->task->which('composer'));
	}

	public function testCanSetPathToPhpAndArtisan()
	{
		$this->app['config'] = $this->getConfig(array(
			'rocketeer::paths.php'     => '/usr/local/bin/php',
			'rocketeer::paths.artisan' => './laravel/artisan',
		));

		$this->assertEquals('/usr/local/bin/php ./laravel/artisan migrate', $this->task->artisan()->migrate());
	}

	public function testFetchesBinaryIfNotSpecifiedOrNull()
	{
		$this->app['config'] = $this->getConfig(array(
			'rocketeer::paths.php' => '/usr/local/bin/php',
		));

		$this->assertEquals('/usr/local/bin/php artisan migrate', $this->task->artisan()->migrate());
	}

	public function testCanGetBinary()
	{
		$whichGrep = exec('which grep');
		$grep      = $this->task->which('grep');

		$this->assertEquals($whichGrep, $grep);
	}

	public function testCanRunComposer()
	{
		$this->usesComposer(true);
		$this->mock('rocketeer.command', 'Illuminate\Console\Command', function ($mock) {
			return $mock
				->shouldIgnoreMissing()
				->shouldReceive('line')
				->shouldReceive('option')->andReturn([]);
		});

		$this->task('Dependencies')->execute();
		$this->assertCount(2, $this->history->getFlattenedHistory()[0]);
	}

	public function testDoesntRunComposerIfNotNeeded()
	{
		$this->usesComposer(false);
		$this->mock('rocketeer.command', 'Illuminate\Console\Command', function ($mock) {
			return $mock
				->shouldIgnoreMissing()
				->shouldReceive('line')
				->shouldReceive('option')->andReturn([]);
		});

		$this->task('Dependencies')->execute();
		$this->assertEmpty($this->history->getFlattenedHistory());
	}
}
