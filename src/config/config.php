<?php return array(

	// Remote access
	//
	// You can either use a single connection or an array of connections
	// For this configure your app/remote.php file
	//////////////////////////////////////////////////////////////////////

	// The remote connection(s) to deploy to
	'connections' => 'production',

	// Git Repository
	//////////////////////////////////////////////////////////////////////

	'git' => array(

		// The SSH/HTTPS adress to your Git Repository
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

		// The name of the application to deploy
		'application_name' => 'application',

		// The number of releases to keep at all times
		'keep_releases' => 4,
	),

	// Tasks
	//////////////////////////////////////////////////////////////////////

	// Here you can define custom tasks to execute after certain actions
	'tasks' => array(

		// Tasks to execute before the core Rocketeer Tasks
		'before' => array(
			'setup'   => array(),
			'deploy'  => array(),
			'cleanup' => array(),
		),

		// Tasks to execute after the core Rocketeer Tasks
		'after' => array(
			'setup'   => array(),
			'deploy'  => array(),
			'cleanup' => array(),
		),

    // Custom Tasks to register with Rocketeer
    'custom' => array(),
	),

);
