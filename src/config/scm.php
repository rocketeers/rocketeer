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

    'scm' => [

        // The SCM used
        // One of "git", "svn", "hg", Required
        'scm' => 'git',

        // The SSH/HTTPS address to your repository
        'repository' => '{scm_repository}',

        // Example: https://github.com/vendor/website.git
        'username' => '{scm_username}',
        'password' => '{scm_password}',

        // The branch to deploy
        'branch' => 'master',

        // Whether your SCM should do a "shallow" clone of the repository or not - this means a clone with just the latest state of your application (no history).
        // If you're having problems cloning, try setting this to false
        'shallow' => true,

        // Recursively pull in submodules.
        // Works only with Git
        'submodules' => true,

    ],

];
