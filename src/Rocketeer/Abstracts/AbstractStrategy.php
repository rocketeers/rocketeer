<?php
namespace Rocketeer\Abstracts;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Rocketeer\Bash;
use Rocketeer\Traits\BashModules\Binaries;
use Rocketeer\Traits\BashModules\Core;
use Rocketeer\Traits\BashModules\Filesystem;
use Rocketeer\Traits\BashModules\Flow;
use Rocketeer\Traits\HasHistory;
use Rocketeer\Traits\HasLocator;

/**
 * Core class for strategies
 */
abstract class AbstractStrategy extends Bash
{
	/**
	 * Display what the command is and does
	 */
	public function displayStatus()
	{
		if (!$this->command) {
			return;
		}

		$components = get_class($this);
		$components = class_basename($components);
		$components = Str::snake($components);
		$components = explode('_', $components);

		$name     = array_get($components, 0);
		$strategy = array_get($components, 1);
		$comment  = sprintf('==> Running strategy for %s: <info>%s</info>', ucfirst($strategy), ucfirst($name));

		$this->command->line($comment);
	}
}
