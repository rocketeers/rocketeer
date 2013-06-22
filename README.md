Rocketeer
=========

## Setup

Rocketeer provides a fast and easy to set-up way to deploy your Laravel projects. **Rocketeer requires Laravel 4.1***.

To use it, add the following to your `composer.json` file :

```json
"anahkiasen/rocketeer": "dev-master"
```

And this line to the `providers` array in your `app/config/app.php` file :

```php
'Rocketeer\RocketeerServiceProvider',
```

Then publish the config :

```
artisan config:publish anahkiasen/rocketeer
```

And you're good to go. Simply edit the config file with the relevant informations.

## Using Rocketeer

The available commands in Rocketeer are :

```
deploy
  deploy:setup                Set up the website for deployment
  deploy:deploy               Deploy the website.
  deploy:cleanup              Clean up old releases from the server
  deploy:current              Displays what the current release is
  deploy:rollback {release}   Rollback to a specific release
  deploy:rollback             Rollback to the previous release
```

## Example config file

```php
<?php return array(

	// Git Repository
	//////////////////////////////////////////////////////////////////////

	'git' => array(

		// The SSH/HTTPS adress to your Git Repository
		'repository' => 'https://bitbucket.org/myUsername/facebook.git',

		// Its credentials
		'username'   => 'myUsername',
		'password'   => 'myPassword',

		// The branch to deploy
		'branch'     => 'master',
	),

	// Remote server
	//////////////////////////////////////////////////////////////////////

	'remote' => array(

		// The root directory where your applications will be deployed
		'root_directory'   => '/home/www/',

		// The default name of the application to deploy
		'application_name' => 'facebook',

		// The number of releases to keep at all times
		'releases' => 4,
	),

	// Tasks
	//////////////////////////////////////////////////////////////////////

	// Here you can define custom tasks to execute after certain actions
	'tasks' => array(

		// Tasks to execute before commands
		'before' => array(),

		// Tasks to execute after commands
		'after' => array(
			'deploy:deploy'  => array(
				'bower install',
				'php artisan basset:build'
			),
		),
	),

);
```