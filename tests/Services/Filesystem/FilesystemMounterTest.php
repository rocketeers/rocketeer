<?php
namespace Rocketeer\Services\Filesystem;

use League\Flysystem\Filesystem;
use Rocketeer\TestCases\RocketeerTestCase;

class FilesystemMounterTest extends RocketeerTestCase
{
    public function testMountsLocalAndRemoteFilesystems()
    {
        $mounter = new FilesystemsMounter($this->app);
        $manager = $mounter->getMountManager();

        $local = $manager->getFilesystem('local');
        $remote = $manager->getFilesystem('remote');

        $this->assertInstanceOf(Filesystem::class, $local);
        $this->assertInstanceOf(Filesystem::class, $remote);
    }
}
