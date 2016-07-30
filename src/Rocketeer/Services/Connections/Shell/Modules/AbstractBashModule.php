<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Services\Connections\Shell\Modules;

use Rocketeer\Services\Connections\Shell\Bash;
use Rocketeer\Services\Modules\AbstractModule;

/**
 * Abstract class for Bash modules.
 */
abstract class AbstractBashModule extends AbstractModule
{
    /**
     * @var Bash
     */
    protected $modulable;
}
