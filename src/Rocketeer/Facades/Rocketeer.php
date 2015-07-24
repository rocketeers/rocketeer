<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Facades;

/**
 * Facade for Rocketeer's CLI.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 *
 * @see    Rocketeer\TasksQueue
 */
class Rocketeer extends StandaloneFacade
{
    /**
     * The class to fetch from the container.
     *
     * @type string
     */
    protected static $accessor = 'rocketeer.tasks';
}
