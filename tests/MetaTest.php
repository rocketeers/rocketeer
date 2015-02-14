<?php
namespace Rocketeer;

use Illuminate\Container\Container;
use Rocketeer\Dummies\Tasks\MyCustomTask;
use Rocketeer\TestCases\RocketeerTestCase;

class MetaTest extends RocketeerTestCase
{
    public function testCanOverwriteTasksViaContainer()
    {
        $this->app->bind('rocketeer.tasks.cleanup', function ($app) {
            return new MyCustomTask($app);
        });

        $this->queue->on('production', ['cleanup'], $this->getCommand());
        $this->assertEquals(['foobar'], $this->history->getFlattenedOutput());
    }

    public function testSingletonsAreProperlyBound()
    {
        $container = new Container();

        $provider = new RocketeerServiceProvider($container);
        $provider->register();
        $provider->boot();

        $bindings = $container->getBindings();
        $this->assertArrayHasKey('rocketeer.remote', $bindings);
        $this->assertTrue($bindings['rocketeer.remote']['shared']);
    }
}
