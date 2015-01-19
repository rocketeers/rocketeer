<?php
namespace Rocketeer\Abstracts;

use Rocketeer\Console\Commands\Plugins\InstallCommand;
use Rocketeer\TestCases\RocketeerTestCase;

class AbstractCommandTest extends RocketeerTestCase
{
    public function testProperlyNamespacesCommands()
    {
        $command = new InstallCommand();
        $command->setLaravel($this->app);
        $this->assertEquals('deploy:plugin-install', $command->getName());

        unset($this->app['artisan']);
        $command = new InstallCommand();
        $command->setLaravel($this->app);
        $this->assertEquals('plugin:install', $command->getName());
    }
}
