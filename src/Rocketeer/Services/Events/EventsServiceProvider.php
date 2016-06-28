<?php
namespace Rocketeer\Services\Events;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Event\Emitter;

class EventsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = ['events'];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('events', function () {
            return new Emitter();
        });
    }
}
