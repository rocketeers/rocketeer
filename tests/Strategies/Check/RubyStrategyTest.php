<?php
namespace Rocketeer\Strategies\Check;

use Mockery;
use Rocketeer\TestCases\RocketeerTestCase;

class RubyStrategyTest extends RocketeerTestCase
{
	/**
	 * @type \Rocketeer\Strategies\Check\PhpStrategy
	 */
	protected $strategy;

	public function setUp()
	{
		parent::setUp();

		$this->strategy = $this->builder->buildStrategy('Check', 'Ruby');
	}

	public function testCanParseLanguageConstraint()
	{
		$manager = Mockery::mock('Bundler', array(
			'getBinary'           => 'bundle',
			'getManifestContents' => '# Some comments'.PHP_EOL."ruby '2.0.0'",
		));
		$this->strategy->setManager($manager);

		$this->mockRemote('1.9.3');
		$this->assertFalse($this->strategy->language());

		$this->mockRemote('2.1.0');
		$this->assertTrue($this->strategy->language());
	}
}
