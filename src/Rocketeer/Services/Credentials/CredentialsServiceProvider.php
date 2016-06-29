<?php
namespace Rocketeer\Services\Credentials;

use League\Container\ServiceProvider\AbstractServiceProvider;

class CredentialsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'credentials.handler',
        'credentials.gatherer',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('credentials.handler', function () {
            return new CredentialsHandler($this->container);
        });

        $this->container->share('credentials.gatherer', function () {
            return new CredentialsGatherer($this->container);
        });
    }
}
