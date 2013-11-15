<?php return array(

	// Remote access
	//
	// You can either use a single connection or an array of connections
	// If this is null, the "default" entry in remote.php will be used
	//////////////////////////////////////////////////////////////////////

	// The remote connection(s) to deploy to
	'connections' => array('production'),

	// SCM repository
	//////////////////////////////////////////////////////////////////////

	'scm' => array(

		// The SCM used (supported: "git", "svn")
		'scm' => 'git',

		// The SSH/HTTPS adress to your repository
		// Example: https://github.com/vendor/website.git
		'repository' => '',

		// The repository credentials : you can leave those empty
		// if you're using SSH or if your repository is public
		// In other cases you can leave this empty too, and you will
		// be prompted for the credentials on deploy
		'username'   => '',
		'password'   => '',

		// The branch to deploy
		'branch'     => 'master',
	),

	// Stages
	//
	// The multiples stages of your application
	// if you don't know what this does, then you don't need it
	//////////////////////////////////////////////////////////////////////

	'stages' => array(

		// Adding entries to this array will split the remote folder in stages
		// Like /var/www/yourapp/staging and /var/www/yourapp/production
		'stages' => array(),

		// The default stage to execute tasks on when --stage is not provided
		'default' => '',
	),

	// Remote server
	//////////////////////////////////////////////////////////////////////

	'remote' => array(

		// Variables about the servers. Those can be guessed but in
		// case of problem it's best to input those manually
		'variables' => array(
			'directory_separator' => '/',
			'line_endings'        => "\n",
		),

		// The root directory where your applications will be deployed
		'root_directory'   => '/home/www/',

		// The name of the application to deploy
		// This will create a folder of the same name in the root directory
		// configured above, so be careful about the characters used
		'application_name' => 'application',

		// The number of releases to keep at all times
		'keep_releases'    => 4,

		// A list of folders/file to be shared between releases
		// Use this to list folders that need to keep their state, like
		// user uploaded data, file-based databases, etc.
		'shared' => array(
			'{path.storage}/logs',
			'{path.storage}/sessions',
		),

		'permissions' => array(

			// The permissions to CHMOD folders to
			// Change to null to leave the folders untouched
			'permissions' => 755,

			// The folders and files to set as web writable
			// You can pass paths in brackets, so {path.public} will return
			// the correct path to the public folder
			'files' => array(
				'app/database/production.sqlite',
				'{path.storage}',
				'{path.public}',
			),

			// The Apache user and group to CHOWN folders to
			// Leave empty to leave the above folders untouched
			'apache' => array(
				'user'  => 'www-data',
				'group' => 'www-data',
			),

		),
	),

	// Tasks
	//
	// Here you can define in the `before` and `after` array, Tasks to execute
	// before or after the core Rocketeer Tasks. You can either put a simple command,
	// a closure which receives a $task object, or the name of a class extending
	// the Rocketeer\Traits\Task class
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

	// Contextual options
	//
	// In this section you can fine-tune the above configuration according
	// to the stage or connection currently in use.
	// Per example :
	// 'stages' => array(
	// 	'staging' => array(
	// 		'scm' => array('branch' => 'staging'),
	// 	),
	//  'production' => array(
	//    'scm' => array('branch' => 'master'),
	//  ),
	// ),

	'on' => array(

		// Stages configurations
		'stages' => array(
		),

		// Connections configuration
		'connections' => array(
		),

	),

);
