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

namespace Rocketeer\Services\Bootstrapper;

use Prophecy\Argument;
use Rocketeer\TestCases\RocketeerTestCase;

class PluginsTest extends RocketeerTestCase
{
    /**
     * @var string
     */
    protected $from;

    /**
     * @var Plugins
     */
    protected $plugins;

    public function setUp()
    {
        parent::setUp();

        $this->plugins = new Plugins($this->container);
        $this->from = $this->container->get('path.base').'/vendor/anahkiasen/rocketeer-slack/src/config';
    }

    public function testCanPublishClassicPluginConfiguration()
    {
        $this->usesLaravel(false);

        $destination = $this->paths->getConfigurationPath().'/plugins/rocketeer-slack';
        $prophecy = $this->bindFilesystemProphecy();
        $prophecy->has(Argument::cetera())->willReturn(true);
        $prophecy->has($destination)->willReturn(false);
        $prophecy->isDirectory(Argument::cetera())->shouldBeCalled()->willReturn(true);
        $prophecy->createDir($destination)->shouldBeCalled()->willReturn(true);
        $prophecy->copyDir($this->from, $destination)->shouldBeCalled()->willReturn(true);

        $this->plugins->publish('anahkiasen/rocketeer-slack');
    }

    public function testCancelsIfNoValidConfigurationPath()
    {
        $this->usesLaravel(false);

        $prophecy = $this->bindFilesystemProphecy();
        $prophecy->isDirectory(Argument::cetera())->willReturn(false);
        $prophecy->has(Argument::cetera())->willReturn(false);
        $prophecy->createDir(Argument::any())->shouldNotBeCalled();

        $this->plugins->publish('anahkiasen/rocketeer-slack');
    }

    public function testCanProperlyFindPackageConfiguration()
    {
        $paths = $this->plugins->findPackageConfiguration('foo/bar');

        $this->assertContains($this->container->get('path.base').'/vendor/foo/bar/src/config', $paths);
        $this->assertContains($this->home.'/.composer/vendor/foo/bar/src/config', $paths);
        $this->assertContains($this->home.'/.rocketeer/vendor/foo/bar/src/config', $paths);
    }
}
