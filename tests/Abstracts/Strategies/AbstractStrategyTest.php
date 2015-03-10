<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Abstracts\Strategies;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractStrategyTest extends RocketeerTestCase
{
    public function testCanCheckForManifestWithoutServer()
    {
        $this->app['path.base'] = realpath(__DIR__.'/../../..');
        $this->swapConfig([
            'paths.app' => realpath(__DIR__.'/../../..'),
        ]);

        $this->usesComposer(false);
        $strategy = $this->builder->buildStrategy('Dependencies', 'Composer');
        $this->assertTrue($strategy->isExecutable());
    }

    public function testCanDisplayStatus()
    {
        $this->expectOutputRegex('#<fg=cyan>\w+</fg=cyan> \| <info>Deploy/Clone</info> <comment>\(.+\)</comment>#');

        $this->mock('rocketeer.command', 'Command', function (MockInterface $mock) {
            return $mock->shouldReceive('line')->andReturnUsing(function ($input) {
                echo $input;
            });
        });

        $strategy = $this->builder->buildStrategy('Deploy', 'Clone');
        $strategy->displayStatus();
    }

    public function testCanGetIdentifier()
    {
        $strategy = $this->builder->buildStrategy('Dependencies');

        $this->assertEquals('strategies.dependencies.polyglot', $strategy->getIdentifier());
    }

    public function testCanFireEvents()
    {
        $this->pretend();

        $this->expectFiredEvent('strategies.dependencies.composer.before');

        $composer = $this->builder->buildStrategy('Dependencies', 'Composer');
        $composer->install();
    }
}
