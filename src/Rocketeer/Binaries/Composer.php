<?php
namespace Rocketeer\Binaries;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractBinary;

class Composer extends AbstractBinary
{
	/**
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		parent::__construct($app);

		$binary = $this->bash->which(
			'composer',
			$this->releasesManager->getCurrentReleasePath().'/composer.phar'
		);

		// Set binary path
		$this->setBinary($binary);
	}

	/**
	 * Change Composer's binary
	 *
	 * @param string $binary
	 */
	public function setBinary($binary)
	{
		parent::setBinary($binary);

		// Prepend PHP command if executing from archive
		if (strpos($this->getBinary(), 'composer.phar') !== false) {
			$php = new Php($this->app);
			$this->setParent($php);
		}
	}
}
