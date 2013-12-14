<?php return array(

	// SCM repository
	//////////////////////////////////////////////////////////////////////

	// The SCM used (supported: "git", "svn")
	'scm' => 'git',

	// The SSH/HTTPS address to your repository
	// Example: https://github.com/vendor/website.git
	'repository' => '{scm_repository}',

	// The repository credentials : you can leave those empty
	// if you're using SSH or if your repository is public
	// In other cases you can leave this empty too, and you will
	// be prompted for the credentials on deploy
	'username'   => '{scm_username}',
	'password'   => '{scm_password}',

	// The branch to deploy
	'branch'     => 'master',

);
