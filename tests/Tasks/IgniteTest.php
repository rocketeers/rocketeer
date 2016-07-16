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
use Rocketeer\Services\Filesystem\FilesystemInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class IgniteTest extends RocketeerTestCase
{
    public function testCanIgniteConfigurationOnWindows()
    {
        $this->mockBasepath('E:\workspace\test', '\\');
        $prophecy = $this->bindProphecy(FilesystemInterface::class, Filesystem::class);

        $this->pretendTask('Ignite')->execute();

        $prophecy->append('E:/workspace/test/.env', Argument::any())->shouldHaveBeenCalled();
        $prophecy->createDir('E:/workspace/test/.rocketeer', Argument::any())->shouldHaveBeenCalled();
    }

    public function testCanIgniteConfiguration()
    {
        $this->mockAnswers([
            'name' => 'foobar',
            'Where is' => 'git@github.com/rocketeers/website.git',
            'SSH key' => true,
        ]);

        $dotenvPath = $this->paths->getDotenvPath();
        $this->files->put($dotenvPath, 'FOO=bar');

        $this->task('Ignite')->execute();

        $config = $this->files->read($this->paths->getConfigurationPath().'/config.php');
        $dotenv = $this->files->read($dotenvPath);

        $this->assertContains("'application_name' => 'foobar'", $config);
        $this->assertContains('FOO=bar', $dotenv);
        $this->assertContains('SCM_REPOSITORY=git@github.com/rocketeers/website.git', $dotenv);
    }
}
