<?php
use Rocketeer\Facades\Rocketeer;

Rocketeer::after('deploy', function ($task) {
	$task->command->comment('Building assets');
	$task->runForCurrentRelease(['npm install', 'node node_modules/.bin/grunt production']);
});