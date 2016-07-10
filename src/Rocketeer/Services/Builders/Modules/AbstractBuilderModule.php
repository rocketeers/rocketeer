<?php
namespace Rocketeer\Services\Builders\Modules;

use League\Container\ContainerAwareInterface;
use Rocketeer\Services\Builders\Builder;
use Rocketeer\Services\Modules\AbstractModule;
use Rocketeer\Traits\ContainerAwareTrait;

abstract class AbstractBuilderModule extends AbstractModule implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Builder
     */
    protected $modulable;
}
