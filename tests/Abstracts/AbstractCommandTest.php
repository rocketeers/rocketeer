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

use Mockery;
use Mockery\MockInterface;
use Rocketeer\Dummies\DummyFailingCommand;
use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AbstractCommandTest extends RocketeerTestCase
{
    public function testGetsProperStatusCodeFromPipelines()
    {
        $this->mock('rocketeer.logs', 'LogsHandler', function (MockInterface $mock) {
            return $mock->shouldReceive('write')->andReturn([]);
        });
        $this->app['rocketeer.credentials'] = Mockery::mock('CredentialsGatherer')->shouldIgnoreMissing();

        $command = new DummyFailingCommand();
        $command->setLaravel($this->app);
        $code = $command->run(new ArrayInput([]), new NullOutput());

        $this->assertEquals(1, $code);
    }
}
