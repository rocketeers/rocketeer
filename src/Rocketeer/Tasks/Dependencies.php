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

class Dependencies extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @type string
     */
    protected $description = 'Installs or update the dependencies on server';

    /**
     * Run the task.
     *
     * @return bool
     */
    public function execute()
    {
        $method       = $this->getOption('update', true) ? 'update' : 'install';
        $dependencies = $this->getStrategy('Dependencies');
        if (!$dependencies) {
            return true;
        }

        return $dependencies->$method();
    }
}
