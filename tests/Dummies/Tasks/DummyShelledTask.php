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

namespace Rocketeer\Dummies\Tasks;

use Rocketeer\Tasks\AbstractTask;

class DummyShelledTask extends AbstractTask
{
    protected $name = 'Shelly';
    protected $description = 'It shells sea shells';

    /**
     * @var array
     */
    protected $options = [
        'shelled' => true,
    ];

    /**
     * Run the task.
     *
     * @return mixed
     */
    public function execute()
    {
        return $this->run([
            'echo "foo"',
            'echo "bar"',
        ]);
    }
}
