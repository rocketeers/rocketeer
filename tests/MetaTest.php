<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Rocketeer\Dummies\Tasks\MyCustomTask;
use Rocketeer\TestCases\RocketeerTestCase;

class MetaTest extends RocketeerTestCase
{
    public function testCanOverwriteTasksViaContainer()
    {
        $this->app->add('rocketeer.tasks.cleanup', function () {
            return new MyCustomTask($this->app);
        });

        $this->queue->on('production', ['cleanup'], $this->getCommand());
        $this->assertEquals(['foobar'], $this->history->getFlattenedOutput());
    }
}
