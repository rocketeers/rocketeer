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

namespace Rocketeer\Abstracts;

use Rocketeer\Scm\Git;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractBinaryTest extends RocketeerTestCase
{
    public function testCanExecuteMethod()
    {
        $this->mock('rocketeer.bash', 'Bash', function ($mock) {
            return $mock->shouldReceive('run')->once()->withAnyArgs()->andReturnUsing(function ($arguments) {
                return $arguments;
            });
        });

        $scm = new Git($this->app);
        $command = $scm->run('checkout', $this->server);
        $expected = $this->replaceHistoryPlaceholders(['git clone "{repository}" "{server}" --branch="master" --depth="1"']);

        $this->assertEquals($expected[0], $command);
    }

    public function testCanUseCustomPathWithScmBinaries()
    {
        $this->swapConfig(['rocketeer::paths.git' => '/foo/bar/git']);

        $git = new Git($this->app);
        $this->assertEquals('/foo/bar/git', $git->getBinary());
    }
}
