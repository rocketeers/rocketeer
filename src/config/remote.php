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
    // Options related to the remote server
    //////////////////////////////////////////////////////////////////////

    'remote' => [

        // Variables about the servers
        'variables' => [
            'directory_separator' => '/',
            'line_endings' => "\n",
        ],

        // Paths/names of folders to use on server.
        // Full path of current release will be at {root_directory}/{app_directory}/{current}/{subdirectory}
        'directories' => [
            // The folder the application will be deployed in.
            // Leave empty to use `application_name` as your folder name
            'app_directory' => null,

            // If the core of your application (ie. where dependencies/migrations/etc.) need to be run is in a subdirectory, specify it there (per example 'my_subdirectory')
            'subdirectory' => null,

            // The name of the folder containing the current release
            'current' => 'current',

            // The name of the folder containing all past and current releases
            'releases' => 'releases',

            // The name of the folder containing files shared between releases
            'shared' => 'shared',
        ],

        // The way symlinks are created
        'symlink' => 'absolute', // One of "absolute", "relative"

        // The number of releases to keep on server at all times
        'keep_releases' => 4,

        // A list of folders/file to be shared between releases
        'shared' => [
            // Examples:
            // 'logs',
            // 'public/uploads',
        ],

        // Files permissions related settings
        'permissions' => [
            // The folders and files to set as web writable
            'files' => [
                // Examples:
                // 'storage',
                // 'public',
            ],

            // What actions will be executed to set permissions on the folder above
            'callback' => function ($file) {
                return [
                    'chmod -R 755 '.$file,
                    'chmod -R g+s '.$file,
                    'chown -R www-data:www-data '.$file,
                ];
            },
        ],

        // Enable use of sudo for some commands
        // You can specify a sudo user by doing
        // 'sudo' => 'the_user'
        'sudo' => true,

        // An array of commands to run under sudo
        'sudoed' => [],

        // If enabled will force a shell to be created which is required for some tools like RVM or NVM
        'shell' => true,

        // An array of commands to run under shell
        'shelled' => [
            // Defaults:
            'which',
            'ruby',
            'npm',
            'bower',
            'bundle',
            'grunt',
        ],

    ],
];
