<?php return array(

	// Remote access
	//
	// You can either use a single connection or an array of connections
	// For this configure your app/remote.php file
	//////////////////////////////////////////////////////////////////////

	// The remote connection(s) to deploy to
	'connections' => array('production'),

	// SCM repository
	//////////////////////////////////////////////////////////////////////

	'scm' => array(

		// The SCM used (supported: "git")
		'scm' => 'git',

		// The SSH/HTTPS adress to your repository
		'repository' => 'https://github.com/vendor/website.git',

		// The repository credentials : you can leave those empty
		// if you're using SSH or if your repository is public
		// In other cases you can leave this empty too, and you will
		// be prompted for the credentials on deploy
		'username'   => 'foo',
		'password'   => 'bar',

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
		'shared' => array(),

		'permissions' => array(

			// The permissions to CHMOD folders to
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

);
