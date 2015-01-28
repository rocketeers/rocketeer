<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class BashTest extends RocketeerTestCase
{
    public function testBashIsCorrectlyComposed()
    {
        $contents = $this->task->runRaw('ls', true, true);
        if (count($contents) !== static::$numberFiles) {
            !dd(count($contents), static::$numberFiles, $contents);
        }

        $this->assertCount(static::$numberFiles, $contents);
    }
}
