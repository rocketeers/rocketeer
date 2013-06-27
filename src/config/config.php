<?php return array(

	// Remote access
	//
	// You can either use a single connection or an array of connections
	// For this configure your app/remote.php file
	//////////////////////////////////////////////////////////////////////

	// The remote connection(s) to deploy to
	'connections' => array('production'),

	// Git Repository
	//////////////////////////////////////////////////////////////////////

	'git' => array(

		// The SSH/HTTPS adress to your Git Repository
		'repository' => 'https://github.com/vendor/website.git',

		// Its credentials – you can leave those empty if you're using SSH
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
		'keep_releases'    => 4,

		// A list of folders/file to be shared between releases
		'shared'           => array(),
	),

	// Tasks
	//
	// Here you can define in the `before` and `after` array, Tasks to execute
	// before or after the core Rocketeer Tasks. You can either put a simple command,
	// a closure which receives a $task object, or the name of a class extending
	// the Rocketeer\Tasks\Task class
	//
	// In the `custom` array you can list custom Tasks classes to be added
	// to Rocketeer. Those will then be available in Artisan
	// as `php artisan deploy:yourtask`
	//////////////////////////////////////////////////////////////////////

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
