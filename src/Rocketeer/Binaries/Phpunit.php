<?php
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
