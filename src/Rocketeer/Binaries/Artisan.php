<?php
namespace Rocketeer\Binaries;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractBinary;

class Artisan extends AbstractBinary
{
	/**
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		parent::__construct($app);

		// Set binary path
		$this->binary = $this->bash->which(
			'artisan',
			$this->releasesManager->getCurrentReleasePath().'/artisan',
			'artisan'
		);

		// Set PHP as parent
		$php = new Php($this->app);
		$this->setParent($php);
	}

	/**
	 * Run outstranding migrations
	 *
	 * @return string
	 */
	public function migrate()
	{
		return $this->getCommand('migrate');
	}

	/**
	 * Seed the database
	 *
	 * @return string
	 */
	public function seed()
	{
		return $this->getCommand('db:seed');
	}

	/**
	 * Clear the cache
	 *
	 * @return string
	 */
	public function clearCache()
	{
		return $this->getCommand('cache:clear');
	}
}
