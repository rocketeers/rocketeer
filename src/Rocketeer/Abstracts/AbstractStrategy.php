<?php
namespace Rocketeer\Abstracts;

use Illuminate\Support\Str;
use Rocketeer\Bash;

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
		// Recompose strategy and implementation from
		// the class name
		$components = get_class($this);
		$components = class_basename($components);
		$components = Str::snake($components);
		$components = explode('_', $components);

		$name     = array_get($components, 0);
		$strategy = array_get($components, 1);

		$object  = 'Running strategy for '.ucfirst($strategy);
		$subject = ucfirst($name);

		$this->explainer->display($object, $subject);
	}
}
