<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Binaries;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractBinary;

class Phpunit extends AbstractBinary
{
	/**
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		parent::__construct($app);

		// Set binary path
		$this->binary = $this->bash->which(
			'phpunit',
			$this->releasesManager->getCurrentReleasePath().'/vendor/bin/phpunit'
		);
	}
}
