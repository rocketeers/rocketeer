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
    // The configuration of your repository
    //////////////////////////////////////////////////////////////////////

    'vcs' => [

        // The VCS used
        'vcs' => 'git', // One of "git", "svn", "hg", Required

        // The SSH/HTTPS address to your repository
        'repository' => null, // Example: https://github.com/vendor/website.git
        'username' => null,
        'password' => null,

        // The branch to deploy
        'branch' => 'master',

        // Whether your VCS should do a "shallow" clone of the repository or not - this means a clone with just the latest state of your application (no history).
        // If you're having problems cloning, try setting this to false
        'shallow' => true,

        // Recursively pull in submodules.
        // Works only with Git
        'submodules' => true,

    ],
];
