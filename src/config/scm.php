<?php

return [

    // SCM repository
    //////////////////////////////////////////////////////////////////////

    // The SCM used (supported: "git", "svn")
    'scm'        => 'git',

    // The SSH/HTTPS address to your repository
    // Example: https://github.com/vendor/website.git
    'repository' => '{scm_repository}',

    // The repository credentials : you can leave those empty
    // if you're using SSH or if your repository is public
    // In other cases you can leave this empty too, and you will
    // be prompted for the credentials on deploy. If you don't want
    // to be prompted (public repo, etc) set the values to null
    'username'   => '{scm_username}',
    'password'   => '{scm_password}',

    // The branch to deploy
    'branch'     => 'master',

    // Whether your SCM should do a "shallow" clone of the repository
    // or not – this means a clone with just the latest state of your
    // application (no history)
    // If you're having problems cloning, try setting this to false
    'shallow'    => true,

    // Recursively pull in submodules. Works only with GIT.
    'submodules' => true,

];
