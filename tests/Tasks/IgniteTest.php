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

use League\Flysystem\Filesystem;
use Prophecy\Argument;
use Rocketeer\Services\Filesystem\LocalFilesystemInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class IgniteTest extends RocketeerTestCase
{
    public function testCanIgniteConfigurationOnWindows()
    {
        $this->mockBasepath('E:\workspace\test', '\\');
        $prophecy = $this->bindProphecy(LocalFilesystemInterface::class, Filesystem::class);

        $this->pretendTask('Ignite')->execute();

        $prophecy->put('E:/workspace/test/.env', Argument::any())->shouldHaveBeenCalled();
        $prophecy->createDir('E:/workspace/test/.rocketeer', Argument::any())->shouldHaveBeenCalled();
    }

    public function testCanIgniteConfiguration()
    {
        $this->task('Ignite', ['ask' => 'foobar', 'confirm' => false])->execute();

        $config = $this->files->read($this->paths->getConfigurationPath().'/config.php');
        $this->assertContains("'application_name' => ''", $config);
    }
}
