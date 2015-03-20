<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Ignition;

use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class PluginsTest extends RocketeerTestCase
{
    /**
     * @type string
     */
    protected $from;

    /**
     * @type Plugins
     */
    protected $plugins;

    public function setUp()
    {
        parent::setUp();

        $this->plugins = new Plugins($this->app);
        $this->from    = $this->app['path.base'].'/vendor/anahkiasen/rocketeer-slack/config';
    }

    public function testCanPublishClassicPluginConfiguration()
    {
        $this->usesLaravel(false);

        $this->mockFiles(function (MockInterface $mock) {
            $destination = $this->app['path.rocketeer.config'].'/plugins/rocketeers/rocketeer-slack';

            return $mock
                ->shouldReceive('has')->with($this->from)->andReturn(true)
                ->shouldReceive('has')->with($destination)->andReturn(false)
                ->shouldReceive('createDir')->with($destination)->andReturn(true)
                ->shouldReceive('copyDirectory')->with($this->from, $destination);
        });

        $this->plugins->publish('anahkiasen/rocketeer-slack');
    }

    public function testCancelsIfNoValidConfigurationPath()
    {
        $this->usesLaravel(false);

        $this->mockFiles(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('has')->with($this->from)->andReturn(false)
                ->shouldReceive('copyDirectory')->never();
        });

        $this->plugins->publish('anahkiasen/rocketeer-slack');
    }

    public function testCanPublishLaravelConfiguration()
    {
        $this->mock('artisan');

        $this->mockFiles(function (MockInterface $mock) {
            $destination = $this->app['path'].'/config/packages/rocketeers/rocketeer-slack';

            return $mock
                ->shouldReceive('has')->with($this->from)->andReturn(true)
                ->shouldReceive('has')->with($destination)->andReturn(false)
                ->shouldReceive('createDir')->with($destination)->andReturn(true)
                ->shouldReceive('copyDirectory')->with($this->from, $destination);
        });

        $this->plugins->publish('anahkiasen/rocketeer-slack');
    }

    public function testCanProperlyFindPackageConfiguration()
    {
        $paths = $this->plugins->findPackageConfiguration('foo/bar');

        $this->assertContains('/src/vendor/foo/bar/src/config', $paths);
        $this->assertContains($this->home.'/.composer/vendor/foo/bar/src/config', $paths);
        $this->assertContains($this->home.'/.rocketeer/vendor/foo/bar/src/config', $paths);
    }
}
