<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    // Options related to the remote server
    //////////////////////////////////////////////////////////////////////

    'remote' => [

        // Variables about the servers
        'variables'      => [
            'directory_separator' => '/',
            'line_endings'        => "\n",
        ],

        // The number of releases to keep on server at all times
        'keep_releases'  => 4,

        // The root directory where your applications will be deployed.
        // This path needs to start at the root, ie. start with a /
        'root_directory' => '/home/www/',

        // The folder the application will be cloned in.
        // Leave empty to use `application_name` as your folder name
        'app_directory'  => null,

        // If the core of your application (ie. where dependencies/migrations/etc need to be run is in a subdirectory, specify it there (per example 'my_subdirectory')
        'subdirectory'   => null,

        // A list of folders/file to be shared between releases
        'shared'         => [
            // Examples:
            // 'logs',
            // 'public/uploads',
        ],

        // The way symlinks are created
        'symlink'        => 'absolute',

        // One of "absolute", "relative"

        // If enabled will force a shell to be created which is required for some tools like RVM or NVM
        'shell'          => true,

        // An array of commands to run under shell
        'shelled'        => [
            // Defaults:
            'which',
            'ruby',
            'npm',
            'bower',
            'bundle',
            'grunt',
        ],

        // Files permissions related settings
        'permissions'    => [

            // The folders and files to set as web writable
            'files'    => [
                // Examples:
                // 'storage',
                // 'public',
            ],

            // what actions will be executed to set permissions on the folder above
            'callback' => function ($task, $file) {
                return [
                    sprintf('chmod -R 755 %s', $file),
                    sprintf('chmod -R g+s %s', $file),
                    sprintf('chown -R www-data:www-data %s', $file),
                ];
            },
        ],
    ],

];
