<?php
namespace Rocketeer\Services\Environment\Modules;

use Rocketeer\TestCases\RocketeerTestCase;

class ServerPathfinderTest extends RocketeerTestCase
{
    public function testCanAppendStuffToFolders()
    {
        $folder = $this->paths->getReleasesFolder();
        $path = $this->paths->getReleasesFolder('foobar');

        $this->assertEquals($folder.'/foobar', $path);
    }
}
