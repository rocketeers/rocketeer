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
    // The multiples stages of your application.
    // If you don't know what this does, then you don't need it
    //////////////////////////////////////////////////////////////////////

    'stages' => [

        // Adding entries to this array will split the remote folder in stages
        // Example: /var/www/yourapp/staging and /var/www/yourapp/production
        'stages' => [
            // Examples:
            // 'staging',
            // 'production',
        ],

        // The default stage to execute tasks on when --stage is not provided.
        // Falsey means all of them
        'default' => [],

    ],
];
