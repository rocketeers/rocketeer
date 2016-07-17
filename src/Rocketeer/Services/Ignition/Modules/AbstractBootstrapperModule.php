<?php
namespace Rocketeer\Services\Ignition\Modules;

use Rocketeer\Services\Ignition\Bootstrapper;
use Rocketeer\Services\Modules\AbstractModule;

abstract class AbstractBootstrapperModule extends AbstractModule
{
    /**
     * @var Bootstrapper
     */
    protected $modulable;
}
