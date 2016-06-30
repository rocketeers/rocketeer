<?php
namespace Rocketeer\Services\History;

use League\Container\ServiceProvider\AbstractServiceProvider;

class HistoryServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        History::class,
        LogsHandler::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(History::class, function () {
            return new History();
        });

        $this->container->share(LogsHandler::class, function () {
            return new LogsHandler($this->container);
        });
    }
}
