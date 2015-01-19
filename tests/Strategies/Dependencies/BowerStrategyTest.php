<?php
namespace Rocketeer\Strategies\Dependencies;

use Mockery\MockInterface;
use Rocketeer\Binaries\PackageManagers\Bower;
use Rocketeer\TestCases\RocketeerTestCase;

class BowerStrategyTest extends RocketeerTestCase
{
    /**
     * @type \Rocketeer\Strategies\Dependencies\BowerStrategy
     */
    protected $bower;

    public function setUp()
    {
        parent::setUp();

        $bower = new Bower($this->app);
        $bower->setBinary('bower');

        $this->bower = $this->builder->buildStrategy('Dependencies', 'Bower');
        $this->bower->setManager($bower);
    }

    public function testCanInstallDependencies()
    {
        $this->pretend();
        $this->bower->install();

        $this->assertHistory(array(
            array(
                'cd {server}/releases/{release}',
                'bower install',
            ),
        ));
    }

    public function testCanUpdateDependencies()
    {
        $this->pretend();
        $this->bower->update();

        $this->assertHistory(array(
            array(
                'cd {server}/releases/{release}',
                'bower update',
            ),
        ));
    }

    public function testUsesAllowRootIfRoot()
    {
        $this->mock('rocketeer.connections', 'Connections', function (MockInterface $mock) {
            return $mock->shouldReceive('getServerCredentials')->andReturn(['username' => 'root']);
        });

        $this->pretend();
        $this->bower->install();

        $this->assertHistory(array(
            array(
                'cd {server}/releases/{release}',
                'bower install --allow-root',
            ),
        ));
    }

    public function testCanGetDependenciesFolder()
    {
        $this->mockFiles(function (MockInterface $mock) {
            return $mock
                ->shouldReceive('exists')->with($this->paths->getUserHomeFolder().'/.bowerrc')->andReturn(true)
                ->shouldReceive('get')->once()->andReturn('{"directory": "components"}');
        });

        $bower = $this->builder->buildBinary('Bower');
        $this->assertEquals('components', $bower->getDependenciesFolder());

        $this->mockFiles(function (MockInterface $mock) {
            return $mock->shouldReceive('exists')->andReturn(false);
        });

        $bower = $this->builder->buildBinary('Bower');
        $this->assertEquals('bower_components', $bower->getDependenciesFolder());
    }
}
