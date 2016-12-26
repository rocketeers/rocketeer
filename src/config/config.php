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
    // The main configuration of your application
    //////////////////////////////////////////////////////////////////////

    'config' => [

        // The name of the application to deploy
        // This will create a folder of the same name in the root directory
        'application_name' => '{application_name}', // Required

        // The schema to use to name log files
        'logs' => function (\Rocketeer\Services\Connections\ConnectionsHandler $connections) {
            return sprintf('%s-%s.log', $connections->getCurrentConnectionKey(), date('Ymd'));
        },

        // Where to store the logs in (relative to the .rocketeer folder)
        'logs_path' => 'logs',

        // The default remote connection(s) to execute tasks on
        'default' => [],
        'connections' => [],

        // In this section you can fine-tune the above configuration according
        // to the stage or connection currently in use.
        // Per example :
        // 'stages' => [
        //     'staging' => [
        //         'vcs' => ['branch' => 'staging'],
        //     ],
        //      'production' => [
        //        'vcs' => ['branch' => 'master'],
        //      ],
        // ],
        'on' => [
            'stages' => [
                // 'name' => [],
            ],
            'connections' => [
                // 'name' => [],
            ],
        ],

    ],
];
