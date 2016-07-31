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
    public function testCanCheckVcsVersionIfRequired()
    {
        $this->usesComposer();

        $this->assertTaskHistory('Check', [
            'git --version',
        ]);
    }

    public function testSkipsVcsCheckIfNotRequired()
    {
        $this->usesComposer();

        $this->swapConfig([
            'strategies.create-release' => 'Sync',
        ]);

        $this->assertTaskHistory('Check', []);
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

        $this->swapConfig([
            'strategies.check' => 'Php',
        ]);
    }

    public function testCanCheckPackageManagerPresence()
    {
        /** @var Composer $manager */
        $manager = $this->prophesize(Composer::class);
        $manager->isExecutable()->willReturn(false);
        $manager->hasManifest()->willReturn(true);
        $manager->getName()->willReturn('Composer');
        $manager->getManifestContents()->willReturn('{}');

        $strategy = $this->builder->buildStrategy('Check', 'Php');
        $strategy->setManager($manager->reveal());
        $this->container->add('rocketeer.strategies.check', $strategy);

        $this->config->set('logs', 'foobar.logs');
        $this->usesComposer();
        $this->bindDummyConnection([
            'which composer' => false,
        ]);

        $this->task('Check')->fire();

        $logs = $this->logs->getLogs();
        $this->assertContains('Composer is not present (or executable)', $logs[6]);
    }

    public function testCanSkipStrategyChecks()
    {
        $this->usesComposer();
        $this->config->set('strategies.check', null);

        $this->pretendTask('Check')->fire();
        $this->assertHistoryNotContains('{php} -m');
    }
}
