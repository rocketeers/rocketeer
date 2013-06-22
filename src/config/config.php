<?php return array(

	// Git Repository
	//////////////////////////////////////////////////////////////////////

	'git' => array(

		// The HTTPS adress to your Git Repository
		'repository' => 'https://github.com/vendor/website.git',

		// Its credentials
		'username'   => 'foo',
		'password'   => 'bar',

		// The branch to deploy
		'branch'     => 'master',
	),

	// Remote server
	//////////////////////////////////////////////////////////////////////

	'remote' => array(

		// The root directory where your applications will be deployed
		'root_directory'   => '/home/www/',

		// The default name of the application to deploy
		'application_name' => 'application',

		// The number of releases to keep at all times
		'releases' => 4,
	),

	// Tasks
	//////////////////////////////////////////////////////////////////////

	// Here you can define custom tasks to execute after certain actions
	'tasks' => array(

	),

);