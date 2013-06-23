Rocketeer [![Build Status](https://travis-ci.org/Anahkiasen/rocketeer.png?branch=master)](https://travis-ci.org/Anahkiasen/rocketeer)
=========

Rocketeer provides a fast and easy way to set-up and deploy your Laravel projects. **Rocketeer requires Laravel 4.1 as it uses the new _illuminate/remote_ component**.

## Using Rocketeer

I recommend you checkout this [Getting Started](https://github.com/Anahkiasen/rocketeer/wiki/Getting-started) guide before anything. It will get you quickly set up to use Rocketeer.

The available commands in Rocketeer are :

```
deploy
	deploy:setup               Set up the website for deployment
	deploy:deploy              Deploy the website.
	deploy:cleanup             Clean up old releases from the server
	deploy:current             Displays what the current release is
	deploy:rollback {release}  Rollback to a specific release
	deploy:rollback            Rollback to the previous release
	deploy:teardown            Removes the remote applications and existing caches
```

## Tasks

An important concept in Rocketeer is Tasks : most of the commands you see right above are Tasks : **Setup**, **Deploy**, etc.
Now, the core of Rocketeer is you can hook into any of those Tasks to peform additional actions, for this you'll use the `before` and `after` arrays of Rocketeer's config file.

A task can be three things :
- A simple one-line command, like `composer install`
- A closure, given access to Rocketeer's core helpers to perform more advanced actions
- And finally a class, extending the `Rocketeer\Tasks\Task` class

So the three kind of tasks above could be seen in your config file :

```php
'before' => array(
	'deploy:setup' => array(

		// Commands
		'composer install',

		// Closures
		function($task) {
			$task->rocketeer->goTo('releases/134781354');
			$tests = $task->run('phpunit');

			if ($tests) {
				$task->command->info('Tests ran perfectly dude !');
			} else {
				$task->command->error('Aw man, tests failed and stuff')
			}
		},

		// Actual Tasks classes
		'MyNamespace\MyTaskClass',
	),
```
