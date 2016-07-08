<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Tasks;

use Mockery\MockInterface;
use Rocketeer\Binaries\PackageManagers\Composer;
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

    /**
     * @param bool   $hasManifest
     * @param string $expected
     *
     * @dataProvider providesManagerStatus
     */
    public function testCanExplicitelySayWhichManagerConditionFailed($hasManifest, $expected)
    {
        /** @var Composer $manager */
        $manager = $this->prophesize(Composer::class);
        $manager->getName()->willReturn('Composer');
        $manager->getManifestContents()->willReturn(null);
        $manager->isExecutable()->willReturn(false);
        $manager->hasManifest()->willReturn($hasManifest);
        $manager->getManifest()->willReturn('composer.json');

        $this->builder->buildStrategy('check')->setManager($manager->reveal());
        $this->task('Check')->fire();
        $this->assertContains('[{username}@production] '.$expected, $this->logs->getLogs());
    }

    public function providesManagerStatus()
    {
        return [
            'Without manifest' => [false, 'No manifest (composer.json) was found for Composer'],
            'With manifest' => [true, 'The Composer package manager could not be found'],
        ];
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
