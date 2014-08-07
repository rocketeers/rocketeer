<?php
namespace Rocketeer\Abstracts;

use Rocketeer\Traits\HasHistory;
use Rocketeer\Traits\HasLocator;

/**
 * Core class for strategies
 */
abstract class AbstractStrategy
{
	use HasLocator;
	use HasHistory;
}
