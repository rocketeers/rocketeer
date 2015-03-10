<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\RocketeerServiceProvider;
use Rocketeer\TestCases\RocketeerTestCase;

class IgniteTest extends RocketeerTestCase
{
    public function testCanIgniteConfigurationOnWindows()
    {
        $this->usesLaravel(false);
        $this->app['path.base'] = 'E:\workspace\test';

        $provider = new RocketeerServiceProvider($this->app);
        $provider->bindPaths();

        $this->mockFiles(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('files')->andReturn([])
                ->shouldReceive('put')->once()->with('E:/workspace/test/.rocketeer', Mockery::any());
        });

        $this->pretendTask('Ignite')->execute();
    }

    public function testCanIgniteConfigurationOnWindowsInLaravel()
    {
        $this->app['path.base'] = 'E:\workspace\test';
        $this->app['path']      = 'E:\workspace\test\app';

        $provider = new RocketeerServiceProvider($this->app);
        $provider->bindPaths();

        $this->mockFiles(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('files')->andReturn([])
                ->shouldReceive('put')->once()->with('E:/workspace/test/app/config/packages/anahkiasen/rocketeer', Mockery::any());
        });

        $this->pretendTask('Ignite')->execute();
    }

    public function testCanIgniteConfigurationOutsideLaravel()
    {
        $command = $this->getCommand(['ask' => 'foobar']);

        $server = $this->server;
        $this->mock('rocketeer.igniter', 'Configuration', function (MockInterface $mock) use ($server) {
            return $mock
                ->shouldReceive('exportConfiguration')->once()->andReturn($server)
                ->shouldReceive('updateConfiguration')->once()->with($server, [
                    'host'             => '{host}',
                    'username'         => '{username}',
                    'password'         => '{password}',
                    'connection'       => 'production',
                    'scm_repository'   => 'https://github.com/'.$this->repository,
                    'scm_username'     => null,
                    'scm_password'     => null,
                    'application_name' => 'foobar',
                ]);
        });

        $this->assertTaskOutput('Ignite', 'Rocketeer configuration was created', $command);
    }
}
