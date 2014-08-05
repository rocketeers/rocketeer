<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class CheckTest extends RocketeerTestCase
{
	public function testCanDoBasicCheck()
	{
		$this->assertTaskHistory('Check', array(
			'git --version',
			'{php} -m',
		));
	}

	public function testCanCheckPhpVersion()
	{
		$check = new Check($this->app);

		$this->mock('files', 'Filesystem', function ($mock) {
			return $mock
				->shouldReceive('put')
				->shouldReceive('glob')->andReturn(array())
				->shouldReceive('exists')->andReturn(true)
				->shouldReceive('get')->andReturn('{"require":{"php":">=5.3.0"}}');
		});
		$this->assertTrue($check->checkPhpVersion());

		// This is is going to come bite me in the ass in 10 years
		$this->mock('files', 'Filesystem', function ($mock) {
			return $mock
				->shouldReceive('put')
				->shouldReceive('glob')->andReturn(array())
				->shouldReceive('exists')->andReturn(true)
				->shouldReceive('get')->andReturn('{"require":{"php":">=5.9.0"}}');
		});
		$this->assertFalse($check->checkPhpVersion());
	}

	public function testCanCheckPhpExtensions()
	{
		$this->swapConfig(array(
			'database.default' => 'sqlite',
			'cache.driver'     => 'redis',
			'session.driver'   => 'apc',
		));

		$this->assertTaskHistory('Check', array(
			'git --version',
			'{php} -m',
		));
	}

	public function testCanCheckForHhvmExtensions()
	{
		$this->app['remote'] = $this->getRemote('HipHop VM 3.0.1 (rel)'.PHP_EOL.'Some more stuff');
		$exists = $this->task('Check')->checkPhpExtension('_hhvm');

		$this->assertTrue($exists);
	}

	public function testCanCheckIfUsesHhvm()
	{
		$hhvm    = $this->task('Check')->usesHhvm();
		$defined = defined('HHVM_VERSION');

		$this->assertEquals($defined, $hhvm);
	}
}
