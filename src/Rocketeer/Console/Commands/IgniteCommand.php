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

namespace Rocketeer\Console\Commands;

/**
 * Ignites the .rocketeer folder onto the
 * user's application.
 */
class IgniteCommand extends BaseTaskCommand
{
    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @var bool
     */
    protected $straight = true;
}
