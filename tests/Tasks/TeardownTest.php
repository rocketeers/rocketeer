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

use Prophecy\Argument;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Services\Storages\Storage;
use Rocketeer\TestCases\RocketeerTestCase;

class TeardownTest extends RocketeerTestCase
{
    public function testCanTeardownServer()
    {
        /** @var Storage $prophecy */
        $prophecy = $this->bindProphecy(Storage::class, 'storage.local');

        $this->assertTaskHistory('Teardown', [
            'rm -rf {server}/',
        ]);

        $prophecy->clear()->shouldHaveBeenCalled();
    }

    public function testCanAbortTeardown()
    {
        /** @var Storage $prophecy */
        $prophecy = $this->bindProphecy(Storage::class, 'storage.local');

        $task = $this->pretendTask('Teardown');
        $commandProphecy = $this->bindProphecy(AbstractCommand::class, 'command');
        $commandProphecy->writeln(Argument::cetera())->willReturn();
        $commandProphecy->confirm(Argument::any())->willReturn(false);
        $commandProphecy->option()->willReturn([]);

        $message = $this->assertTaskHistory($task, []);
        $this->assertContains('Teardown aborted', $message);
        $prophecy->clear()->shouldNotHaveBeenCalled();
    }
}
