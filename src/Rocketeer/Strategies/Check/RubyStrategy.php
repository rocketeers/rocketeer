<?php
namespace Rocketeer\Strategies\Check;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Rocketeer\Abstracts\Strategies\AbstractCheckStrategy;
use Rocketeer\Abstracts\Strategies\AbstractStrategy;
use Rocketeer\Interfaces\Strategies\CheckStrategyInterface;

class RubyStrategy extends AbstractCheckStrategy implements CheckStrategyInterface
{
	/**
	 * The language of the strategy
	 *
	 * @type string
	 */
	protected $language = 'Ruby';

	/**
	 * The PHP extensions loaded on server
	 *
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app     = $app;
		$this->manager = $this->binary('bundler');
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////////// CHECKS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Check that the language used by the
	 * application is at the required version
	 *
	 * @return boolean
	 */
	public function language()
	{
		$required = null;

		// Get the minimum Ruby version of the application
		if ($gemfile = $this->manager->getManifestContents()) {
			preg_match('/ruby \'(.+)\'/', $gemfile, $matches);
			$required = Arr::get($matches, 1);
		}

		// Cancel if no Ruby version found
		if (!$required) {
			return true;
		}

		$version = $this->binary('ruby')->run('--version');
		$version = preg_replace('/ruby ([0-9\.]+)p?.+/', '$1', $version);

		return version_compare($version, $required, '>=');
	}

	/**
	 * Check for the required extensions
	 *
	 * @return array
	 */
	public function extensions()
	{
		return [];
	}

	/**
	 * Check for the required drivers
	 *
	 * @return array
	 */
	public function drivers()
	{
		return [];
	}
}
