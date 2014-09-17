<?php
namespace Rocketeer\Strategies\Check;

use Rocketeer\TestCases\RocketeerTestCase;

class PhpStrategyTest extends RocketeerTestCase
{
	/**
	 * @type \Rocketeer\Strategies\Check\PhpStrategy
	 */
	protected $strategy;

	public function setUp()
	{
		parent::setUp();

		$this->strategy = $this->builder->buildStrategy('Check', 'Php');
	}

	public function testCanCheckPhpVersion()
	{
		$this->mockFiles(function ($mock) {
			return $mock
				->shouldReceive('put')
				->shouldReceive('glob')->andReturn(array())
				->shouldReceive('exists')->andReturn(true)
				->shouldReceive('get')->andReturn('{"require":{"php":">=5.3.0"}}');
		});
		$this->assertTrue($this->strategy->language());

		// This is is going to come bite me in the ass in 10 years
		$this->mockFiles(function ($mock) {
			return $mock
				->shouldReceive('put')
				->shouldReceive('glob')->andReturn(array())
				->shouldReceive('exists')->andReturn(true)
				->shouldReceive('get')->andReturn('{"require":{"php":">=5.9.0"}}');
		});
		$this->assertFalse($this->strategy->language());
	}

	public function testCanCheckPhpExtensions()
	{
		$this->swapConfig(array(
			'database.default' => 'sqlite',
			'cache.driver'     => 'redis',
			'session.driver'   => 'apc',
		));

		$this->strategy->extensions();

		$this->assertHistory(['{php} -m']);
	}

	public function testCanCheckForHhvmExtensions()
	{
		$this->mockRemote('HipHop VM 3.0.1 (rel)'.PHP_EOL.'Some more stuff');
		$exists = $this->strategy->checkPhpExtension('_hhvm');

		$this->assertTrue($exists);
	}
}
