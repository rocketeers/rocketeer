<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Tasks;

use Rocketeer\Abstracts\AbstractTask;

/**
 * After command task.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class After extends AbstractTask
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'After command task';


    /**
     * Run the task.
     *
     * @return bool
     */
    public function execute()
    {
        // Do nothing
        return true;
    }

}
