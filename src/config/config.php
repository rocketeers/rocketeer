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

        // The default remote connection(s) to execute tasks on
        'default' => [
            // Default:
            'production',
        ],

        // You can leave all of this empty or remove it entirely if you don't want
        // to track files with credentials : Rocketeer will prompt you for your credentials
        // and store them locally.
        // There are four ways to define a credential:
        // 'foobar'   - value is required, will never be prompted for it
        // ''         - value is required, will be prompted for it once, then saved
        // true       - value is required, will be prompted for it every time
        // false|null - value is not required, will never be prompted for it
        'connections' => [],

        // In this section you can fine-tune the above configuration according
        // to the stage or connection currently in use.
        // Per example :
        // 'stages' => [
        //     'staging' => [
        //         'scm' => ['branch' => 'staging'],
        //     ],
        //      'production' => [
        //        'scm' => ['branch' => 'master'],
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
