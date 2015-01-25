<?php
namespace Rocketeer\Tasks;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class CheckTest extends RocketeerTestCase
{
    public function testCanCheckScmVersionIfRequired()
    {
        $this->usesComposer(true);

        $this->assertTaskHistory('Check', array(
            'git --version',
            '{php} -m',
        ));
    }

    public function testSkipsScmCheckIfNotRequired()
    {
        $this->usesComposer(true);

        $this->swapConfig(array(
            'rocketeer::strategies.deploy' => 'sync',
        ));

        $this->assertTaskHistory('Check', array(
            '{php} -m',
        ));
    }

    public function testStopsCheckingIfErrorOccured()
    {
        $this->mock('rocketeer.strategies.check', 'Rocketeer\Abstracts\Strategies\AbstractCheckStrategy', function (MockInterface $mock) {
           return $mock
               ->shouldReceive('isExecutable')->andReturn(true)
               ->shouldReceive('displayStatus')->andReturnSelf()
               ->shouldReceive('manager')->andReturn(true)
               ->shouldReceive('language')->andReturn(false)
               ->shouldReceive('extensions')->never();
        });

        $this->swapConfig(array(
            'rocketeer::strategies.check' => 'Php',
        ));
    }
}
