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

use League\Flysystem\Filesystem;
use Prophecy\Argument;
use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractDependenciesStrategyTest extends RocketeerTestCase
{
    public function testCanShareDependenciesFolder()
    {
        $prophecy = $this->bindProphecy(Bash::class);

        $bower = $this->builder->buildStrategy('Dependencies', 'Bower');

        /** @var Filesystem $files */
        $files = $this->bindFilesystemProphecy();
        $files->read(Argument::any())->willReturn();
        $files->has(Argument::any())->willReturn();
        $files->has($this->paths->getUserHomeFolder().'/.bowerrc')->willReturn(true);

        $this->pretend();
        $bower->configure(['shared_dependencies' => true]);
        $bower->install();

        $prophecy->share('bower_components')->shouldHaveBeenCalled();
    }

    public function testCanCopyDependencies()
    {
        $prophecy = $this->bindProphecy(Bash::class);

        $bower = $this->builder->buildStrategy('Dependencies', 'Bower');

        /** @var Filesystem $files */
        $files = $this->bindFilesystemProphecy();
        $files->read(Argument::any())->willReturn();
        $files->has(Argument::any())->willReturn();
        $files->has($this->paths->getUserHomeFolder().'/.bowerrc')->willReturn(true);

        $this->pretend();
        $bower->configure(['shared_dependencies' => 'copy']);
        $bower->install();

        $prophecy->copyFromPreviousRelease('bower_components')->shouldHaveBeenCalled();
    }
}
