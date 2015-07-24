<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Tasks;

use Illuminate\Support\Fluent;

/**
 * A job storing where a task/multiple tasks need to be executed.
 *
 * @property string                              connection
 * @property integer                             server
 * @property string|null                         stage
 * @property \Rocketeer\Abstracts\AbstractTask[] queue
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Job extends Fluent
{
    // ...
}
