<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer;

use Rocketeer\Traits\BashModules\Binaries;
use Rocketeer\Traits\BashModules\Core;
use Rocketeer\Traits\BashModules\Filesystem;
use Rocketeer\Traits\BashModules\Flow;

/**
 * An helper to execute low-level commands on the remote server
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Bash
{
	use Core;
	use Binaries;
	use Filesystem;
	use Flow;

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// RUNNERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the implementation behind a strategy
	 *
	 * @param string      $strategy
	 * @param string|null $concrete
	 * @param bool        $force
	 *
	 * @return \Rocketeer\Abstracts\AbstractStrategy
	 */
	public function getStrategy($strategy, $concrete = null, $force = false)
	{
		$strategy = $this->builder->buildStrategy($strategy, $concrete);
		if (!$strategy->isExecutable() and !$force) {
			return;
		}

		return $this->explainer->displayBelow(function () use ($strategy) {
			return $strategy->displayStatus();
		});
	}

	/**
	 * Execute another AbstractTask by name
	 *
	 * @param string|string[] $tasks
	 *
	 * @return string|false
	 */
	public function executeTask($tasks)
	{
		return $this->explainer->displayBelow(function () use ($tasks) {
			return $this->queue->run($tasks);
		});
	}
}
