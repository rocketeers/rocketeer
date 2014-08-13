<?php
use Rocketeer\Binaries\Composer;

return array(

	// Task strategies
	//
	// Here you can configure in a modular way which tasks to use to
	// execute various core parts of your deployment's flow
	//////////////////////////////////////////////////////////////////////

	// Which strategy to use to create a new release
	'deploy'       => 'Clone',

	// Which strategy to use to test your application
	'test'         => 'Phpunit',

	// Which strategy to use to migrate your database
	'migrate'      => 'Artisan',

	// Which strategy to use to install your application's dependencies
	'dependencies' => 'Composer',

	// Execution hooks
	//////////////////////////////////////////////////////////////////////

	'composer' => array(
		'install' => function (Composer $composer, Task $task) {
			return $composer->install([], ['--no-interaction' => null, '--no-dev' => null, '--prefer-dist' => null]);
		},
		'update' => function (Composer $composer) {
			return $composer->update();
		},
	),

);
