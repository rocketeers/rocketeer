<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class SwapSymlinkTest extends RocketeerTestCase
{
    public function testCanSwapCurrentSymlink()
    {
        $matcher = [[
            'ln -s {server}/releases/{release} {server}/current-temp',
            'mv -Tf {server}/current-temp {server}/current',
        ]];

        $results = $this->assertTaskHistory('SwapSymlink', $matcher);
        $this->assertTrue($results);
    }
}
