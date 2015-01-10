<?php
namespace Rocketeer\Services\Storages;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class LocalStorageTest extends RocketeerTestCase
{
    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// TESTS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function testCanDestroyFile()
    {
        $file = $this->localStorage->getFilepath();
        $this->localStorage->destroy();

        $this->assertFileNotExists($file);
    }

    public function testCanCreateDeploymentsFileAnywhere()
    {
        $this->app['path.storage'] = null;
        $this->app->offsetUnset('path.storage');

        new LocalStorage($this->app);

        $storage = $this->paths->getRocketeerConfigFolder();
        $exists  = file_exists($storage);
        $this->files->deleteDirectory($storage);
        $this->assertTrue($exists);
    }

    public function testCanComputeHashAccordingToContentsOfFiles()
    {
        $this->mock('rocketeer.paths', 'Pathfinder', function (MockInterface $mock) {
           return $mock->shouldReceive('getConfigurationPath')->andReturn($this->server);
        });

        $this->files->put($this->server.'/bar.php', '<?php return ["bar"];');
        $this->files->put($this->server.'/foo.php', '<?php return ["foo"];');
        $this->files->put($this->server.'/tasks.php', '<?php return ["tasks"];');

        $storage = new LocalStorage($this->app, 'deployments', $this->server);
        $hash    = $storage->getHash();

        $this->assertEquals(md5('["bar"]["foo"]'), $hash);
    }

    public function testCanSwitchFolder()
    {
        $storage = new LocalStorage($this->app, 'foo', '/foo');
        $storage->setFolder($this->server);
        $file = $storage->getFilepath();

        $this->assertEquals($this->server, $storage->getFolder());
        $this->assertEquals($this->server.'/foo.json', $file);
    }

    public function testCanDelegateMethodsToEnvironment()
    {
        $this->localStorage->destroy();

        $this->assertEquals(PHP_EOL, $this->localStorage->getLineEndings());
    }
}
