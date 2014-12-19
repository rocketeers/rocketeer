<?php
namespace Rocketeer\Console;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class SelfUpdaterTest extends RocketeerTestCase
{
	public function testCanUpdateToLatestVersion()
	{
		$this->mockFiles(function (MockInterface $mock) {
			return $mock
				->shouldReceive('isWritable')->twice()->andReturn(true)
				->shouldReceive('move')->with($this->paths->getRocketeerConfigFolder().'/bar-latest-temp.phar', '/foo/bar')->once();
		});

		$curl = $this->mockCurl('rocketeer.phar', 'latest');

		$updater = new SelfUpdater($this->app, '/foo/bar');
		$updater->setCurl($curl);
		$updater->update();
	}

	public function testCanUpdateToSpecificVersion()
	{
		$this->mockFiles(function (MockInterface $mock) {
			return $mock
				->shouldReceive('isWritable')->twice()->andReturn(true)
				->shouldReceive('move')->with($this->paths->getRocketeerConfigFolder().'/bar-1.0.4-temp.phar', '/foo/bar')->once();
		});

		$curl = $this->mockCurl('rocketeer1.0.4.phar', '1.0.4');

		$updater = new SelfUpdater($this->app, '/foo/bar', '1.0.4');
		$updater->setCurl($curl);
		$updater->update();
	}

	/**
	 * @param $input
	 * @param $output
	 *
	 * @return MockInterface
	 */
	protected function mockCurl($input, $output)
	{
		return Mockery::mock('anlutro\cURL\cURL')
		              ->shouldReceive('newRequest')->with('GET', 'http://rocketeer.autopergamene.eu/versions/'.$input, Mockery::any())->andReturnSelf()
		              ->shouldReceive('send')->andReturn($output)
		              ->mock();
	}
}
