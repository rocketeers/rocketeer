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

return [
    // Here you can customize Rocketeer by adding tasks, strategies, etc.
    //////////////////////////////////////////////////////////////////////

    'hooks' => [
        'events' => [
            'before' => [
                // Here you can configure the Primer tasks which will run a set of commands on the local machine, determining whether the deploy can proceed or not
                'primer' => function (\Rocketeer\Tasks\Subtasks\Primer $task) {
                    return [
                // $task->executeTask('Test'),
                // $task->binary('grunt')->execute('lint'),
            ];
                },
            ],
            'after' => [
                // 'name' => null,
            ],
        ],

        // Here you can quickly add custom tasks to Rocketeer, as well as to its CLI
        'tasks' => [
            // 'name' => null,
        ],

        // Define roles to assign to tasks
        // eg. 'db' => ['Migrate']
        'roles' => [
            // 'name' => null,
        ],
    ],

];
