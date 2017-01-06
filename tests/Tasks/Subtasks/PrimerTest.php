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

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class PrimerTest extends RocketeerTestCase
{
    public function testCanExecutePrimerTasks()
    {
        $this->swapConfig([
            'rocketeer::default' => 'production',
            'rocketeer::strategies.primer' => function () {
                return 'ls';
            },
        ]);

        $this->assertTaskHistory('Primer', ['ls']);
    }
}
