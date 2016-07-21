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

use Rocketeer\Binaries\PackageManagers\Composer;
use Rocketeer\Strategies\Check\PhpStrategy;
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

        $this->mockConfig([
            'strategies.deploy' => 'sync',
        ]);

        $this->assertTaskHistory('Check', [
            '{php} -m',
        ]);
    }

    public function testStopsCheckingIfErrorOccured()
    {
        /** @var PhpStrategy $prophecy */
        $prophecy = $this->bindProphecy(PhpStrategy::class, 'rocketeer.strategies.check');
        $prophecy->isExecutable()->willReturn(true);
        $prophecy->manager()->willReturn(true);
        $prophecy->language()->willReturn(false);
        $prophecy->extensions()->shouldNotBeCalled();
        $prophecy->displayStatus()->will(function () {
            return $this;
        });

        $this->mockConfig([
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
        $this->config->set('logs', 'foobar.logs');

        /** @var Composer $manager */
        $manager = $this->prophesize(Composer::class);
        $manager->getName()->willReturn('Composer');
        $manager->getManifestContents()->willReturn(null);
        $manager->isExecutable()->willReturn(false);
        $manager->hasManifest()->willReturn($hasManifest);
        $manager->getManifest()->willReturn('composer.json');

        $this->builder->buildStrategy('check')->setManager($manager->reveal());
        $this->task('Check')->fire();

        $logs = $this->logs->getLogs();
        $this->assertContains('{username}@production: '.$expected, last($logs));
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
        $this->usesComposer();
        $this->config->set('strategies.check', null);

        $this->pretendTask('Check')->fire();
        $this->assertHistoryNotContains('{php} -m');
    }
}
