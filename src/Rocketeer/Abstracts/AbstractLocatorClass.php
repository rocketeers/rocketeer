<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Abstracts;

use Rocketeer\Traits\HasLocator;

/**
 * An abstract for Service Locator-based classes with adds
 * a few shortcuts to Rocketeer classes
 *
 * @property \Rocketeer\ConnectionsHandler    connections
 * @property \Illuminate\Console\Command      command
 * @property \Illuminate\Remote\Connection    remote
 * @property \Rocketeer\ReleasesManager       releasesManager
 * @property \Rocketeer\Rocketeer             rocketeer
 * @property \Rocketeer\Server                server
 * @property \Rocketeer\Abstracts\Scm         scm
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractLocatorClass
{
	use HasLocator;
}
