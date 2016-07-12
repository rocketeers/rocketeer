<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class CreateReleaseTest extends RocketeerTestCase
{
    public function testAddsDeployedReleaseToList()
    {
        $this->pretend();
        $this->task('CreateRelease')->execute();

        $this->assertCount(4, $this->releasesManager->getReleases());
    }
}
