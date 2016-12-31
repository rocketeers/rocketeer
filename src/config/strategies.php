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
    // Here you can configure in a modular way which tasks to use to execute various core parts of your deployment's flow
    //////////////////////////////////////////////////////////////////////

    'strategies' => [

        // Which strategy to use to check the server
        'check' => 'Polyglot',

        // Which strategy to use to create a new release and prepare its dependencies
        'create-release' => 'Clone',

        // Which strategy to use to send a release online
        'deploy' => 'Rolling',

        // Which strategy to use to rollback to a previous release
        'rollback' => 'Rolling',

        // Which strategy to use to test your application
        'test' => 'Phpunit',

        // Which strategy to use to migrate your database
        'migrate' => null,

        // Which strategy to use to install your application's dependencies
        'dependencies' => 'Polyglot',

    ],
];
