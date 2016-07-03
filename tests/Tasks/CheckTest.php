<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Strategies\AbstractCheckStrategy;
use Rocketeer\TestCases\RocketeerTestCase;

class CheckTest extends RocketeerTestCase
{
    public function testCanCheckScmVersionIfRequired()
    {
        $this->usesComposer();

        $this->assertTaskHistory('Check', [
            'git --version',
            '{php} -m',
        ]);
    }

    public function testSkipsScmCheckIfNotRequired()
    {
        $this->usesComposer();

        $this->swapConfig([
            'strategies.deploy' => 'sync',
        ]);

        $this->assertTaskHistory('Check', [
            '{php} -m',
        ]);
    }

    public function testStopsCheckingIfErrorOccured()
    {
        $this->mock('rocketeer.strategies.check', AbstractCheckStrategy::class, function (
            MockInterface $mock
        ) {
            return $mock
                ->shouldReceive('isExecutable')->andReturn(true)
                ->shouldReceive('displayStatus')->andReturnSelf()
                ->shouldReceive('manager')->andReturn(true)
                ->shouldReceive('language')->andReturn(false)
                ->shouldReceive('extensions')->never();
        });

        $this->swapConfig([
            'strategies.check' => 'Php',
        ]);
    }

    public function testCanExplicitelySayWhichManagerConditionFailed()
    {
        $manager = Mockery::mock('Composer', [
            'getName' => 'Composer',
            'getManifestContents' => null,
            'isExecutable' => false,
            'hasManifest' => false,
            'getManifest' => 'composer.json',
        ]);
        $this->builder->buildStrategy('check')->setManager($manager);
        $this->task('Check')->fire();
        $this->assertContains('[{username}@production] No manifest (composer.json) was found for Composer', $this->logs->getLogs());

        $manager = Mockery::mock('Composer', [
            'getName' => 'Composer',
            'getManifestContents' => null,
            'isExecutable' => false,
            'hasManifest' => true,
            'getManifest' => 'composer.json',
        ]);
        $this->container->get('rocketeer.strategies.check')->setManager($manager);
        $this->task('Check')->fire();
        $this->assertContains('[{username}@production] The Composer package manager could not be found', $this->logs->getLogs());
    }

    public function testCanSkipStrategyChecks()
    {
        $this->pretend();
        $this->usesComposer();
        $this->config->set('strategies.check', null);

        $this->task('Check')->fire();
        $this->assertHistoryNotContains('{php} -m');
    }
}
