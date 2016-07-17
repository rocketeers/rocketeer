<?php
namespace Rocketeer;

use League\Container\ServiceProvider\ServiceProviderInterface;

class ServiceProviderAggregate extends \League\Container\ServiceProvider\ServiceProviderAggregate
{
    /**
     * @var string[]
     */
    protected $added;

    /**
     * {@inheritdoc}
     */
    public function add($provider)
    {
        // Unify to instances
        if (is_string($provider) && class_exists($provider)) {
            $provider = new $provider;
        }

        $this->added[] = $provider;

        return parent::add($provider);
    }

    /**
     * @return ServiceProviderInterface[]
     */
    public function getAdded()
    {
        return $this->added;
    }
}
