<?php
namespace Rocketeer\Tasks;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class CheckTest extends RocketeerTestCase
{
    public function testCanCheckScmVersionIfRequired()
    {
        $this->usesComposer();

        $this->assertTaskHistory('Check', array(
            'git --version',
            '{php} -m',
        ));
    }

    public function testSkipsScmCheckIfNotRequired()
    {
        $this->usesComposer();

        $this->swapConfig(array(
            'rocketeer::strategies.deploy' => 'sync',
        ));

        $this->assertTaskHistory('Check', array(
            '{php} -m',
        ));
    }

    public function testStopsCheckingIfErrorOccured()
    {
        $this->mock('rocketeer.strategies.check', 'Rocketeer\Abstracts\Strategies\AbstractCheckStrategy', function (
            MockInterface $mock
        ) {
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

    public function testCanExplicitelySayWhichManagerConditionFailed()
    {
        $manager = Mockery::mock('Composer', [
            'getName'             => 'Composer',
            'getManifestContents' => null,
            'isExecutable'        => false,
            'hasManifest'         => false,
            'getManifest'         => 'composer.json',
        ]);
        $this->app['rocketeer.strategies.check']->setManager($manager);
        $this->task('Check')->fire();
        $this->assertContains('[{username}@production] No manifest (composer.json) was found for Composer', $this->logs->getLogs());

        $manager = Mockery::mock('Composer', [
            'getName'             => 'Composer',
            'getManifestContents' => null,
            'isExecutable'        => false,
            'hasManifest'         => true,
            'getManifest'         => 'composer.json',
        ]);
        $this->app['rocketeer.strategies.check']->setManager($manager);
        $this->task('Check')->fire();
        $this->assertContains('[{username}@production] The Composer package manager could not be found', $this->logs->getLogs());
    }

    public function testCanSkipStrategyChecks()
    {
        $this->pretend();
        $this->usesComposer();
        unset($this->app['rocketeer.strategies.check']);

        $this->task('Check')->fire();
        $this->assertHistoryNotContains('{php} -m');
    }
}
