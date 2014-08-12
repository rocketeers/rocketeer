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
	 * @param bool $seed
	 *
	 * @return string
	 */
	public function migrate($seed = false)
	{
		$this->command->comment('Running outstanding migrations');
		$flags = $seed ? ['--seed' => null] : [];

		return $this->getCommand('migrate', [], $flags);
	}

	/**
	 * Seed the database
	 *
	 * @param string|null $class A class to seed
	 *
	 * @return string
	 */
	public function seed($class = null)
	{
		$this->command->comment('Seeding database');
		$flags = $class ? ['--class' => $class] : [];

		return $this->getCommand('db:seed', [], $flags);
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
