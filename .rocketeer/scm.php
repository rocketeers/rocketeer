<?php return array(

	// SCM repository
	//////////////////////////////////////////////////////////////////////

	// The SCM used (supported: "git", "svn")
	'scm' => 'git',

	// The SSH/HTTPS address to your repository
	// Example: https://github.com/vendor/website.git
	'repository' => 'https://github.com/Anahkiasen/rocketeer.git',

	// The repository credentials : you can leave those empty
	// if you're using SSH or if your repository is public
	// In other cases you can leave this empty too, and you will
	// be prompted for the credentials on deploy
	'username'   => 'Anahkiasen',
	'password'   => '',

	// The branch to deploy
	'branch'     => 'gh-pages',

	// Whether your SCM should do a "shallow" clone of the repository
	// or not – this means a clone with just the latest state of your
	// application (no history)
	// If you're having problems cloning, try setting this to false
	'shallow' => true,

);
