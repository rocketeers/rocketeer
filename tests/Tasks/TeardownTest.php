<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

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

        $prophecy->destroy()->shouldHaveBeenCalled();
    }

    public function testCanAbortTeardown()
    {
        /** @var Storage $prophecy */
        $prophecy = $this->bindProphecy(Storage::class, 'storage.local');

        $task = $this->pretendTask('Teardown', [], ['confirm' => false]);
        $message = $this->assertTaskHistory($task, []);

        $this->assertContains('Teardown aborted', $message);
        $prophecy->destroy()->shouldNotHaveBeenCalled();
    }
}
