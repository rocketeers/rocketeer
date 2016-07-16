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

namespace Rocketeer\Strategies\Dependencies;

use Mockery\MockInterface;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractDependenciesStrategyTest extends RocketeerTestCase
{
    public function testCanShareDependenciesFolder()
    {
        $prophecy = $this->bindProphecy(Bash::class);

        $bower = $this->builder->buildStrategy('Dependencies', 'Bower');

        $this->mockFiles(function (MockInterface $mock) {
            return $mock->shouldReceive('has')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true);
        });

        $this->pretend();
        $bower->configure(['shared_dependencies' => true]);
        $bower->install();

        $prophecy->share('bower_components')->shouldHaveBeenCalled();
    }

    public function testCanCopyDependencies()
    {
        $prophecy = $this->bindProphecy(Bash::class);

        $bower = $this->builder->buildStrategy('Dependencies', 'Bower');

        $this->mockFiles(function (MockInterface $mock) {
            return $mock->shouldReceive('has')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true);
        });

        $this->pretend();
        $bower->configure(['shared_dependencies' => 'copy']);
        $bower->install();

        $prophecy->copyFromPreviousRelease('bower_components')->shouldHaveBeenCalled();
    }
}
