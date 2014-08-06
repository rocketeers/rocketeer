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
}
