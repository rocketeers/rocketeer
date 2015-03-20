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

class AbstractDependenciesStrategyTest extends RocketeerTestCase
{
    public function testCanShareDependenciesFolder()
    {
        $bower = $this->builder->buildStrategy('Dependencies', 'Bower');

        $this->mockFiles(function (MockInterface $mock) {
            return $mock->shouldReceive('has')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true);
        });

        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('share')->once()->with('bower_components');
        });

        $this->pretend();
        $bower->configure(['shared_dependencies' => true]);
        $bower->install();
    }

    public function testCanCopyDependencies()
    {
        $bower = $this->builder->buildStrategy('Dependencies', 'Bower');

        $this->mockFiles(function (MockInterface $mock) {
            return $mock->shouldReceive('has')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true);
        });

        $this->mock('rocketeer.bash', 'Bash', function (MockInterface $mock) {
            return $mock->shouldReceive('copyFromPreviousRelease')->once()->with('bower_components');
        });

        $this->pretend();
        $bower->configure(['shared_dependencies' => 'copy']);
        $bower->install();
    }
}
