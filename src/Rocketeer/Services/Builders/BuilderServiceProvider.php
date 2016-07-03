<?php
namespace Rocketeer\Services\Builders;

use League\Container\ServiceProvider\AbstractServiceProvider;

class BuilderServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Builder::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share(Builder::class)->withArgument($this->container);
    }
}
