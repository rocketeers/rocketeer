<?php
use Rocketeer\Facades\Rocketeer;

Rocketeer::after('deploy', function ($task) {
	$task->command->comment('Installing components');
	$task->runForCurrentRelease(['bower install --allow-root', 'npm install']);

	$task->command->comment('Building assets');
	$task->runForCurrentRelease('node node_modules/.bin/grunt production');
});