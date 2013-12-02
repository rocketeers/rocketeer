<?php
namespace Rocketeer\Console;

use Herrera\Box\Box;
use Herrera\Box\Compactor\Php;
use Herrera\Box\StubGenerator;
use Phar;
use Symfony\Component\Finder\Finder;

class Compiler
{
	/**
	 * The current Box instance
	 *
	 * @var Box
	 */
	protected $box;

	/**
	 * Extract an existing Phar
	 *
	 * @param string $phar
	 *
	 * @return void
	 */
	public function extract($phar, $destination)
	{
		if (file_exists($destination)) {
			$this->removeFolder($destination);
		}

		$phar = new Phar($phar);
		$phar->extractTo($destination);
	}

	/**
	 * Compile the final PHAR
	 *
	 * @param string $phar
	 *
	 * @return void
	 */
	public function compile($phar = 'rocketeer.phar')
	{
		// Remove existing PHAR
		if (file_exists($phar)) {
			unlink($phar);
		}

		// Store some path variables
		$root   = __DIR__.'/../../..';
		$src    = $root.'/src';
		$vendor = $root.'/vendor';

		// Create Box
		$this->box = Box::create($phar, 0, basename($phar));

		// Add compactors
		$this->box->addCompactor(new Php);

		// Add core files and dependencies
		$this->addFolder($src);
		$this->addFolder($vendor, array(
			'mockery',
			'patchwork',
			'herrera-io',
			'nesbot',
		));

		// Add binary
		$binary = file_get_contents($root.'/bin/rocketeer');
		$binary = str_replace('#!/usr/bin/env php', null, $binary);
		$this->box->addFromString('bin/rocketeer', $binary);

		// Set stub
		$this->setStub();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Remove a folder and all of its contents
	 *
	 * @param string $folder
	 *
	 * @return vodi
	 */
	protected function removeFolder($folder)
	{
    foreach (glob($folder.'/*') as $file) {
      if (is_dir($file)) {
      	$this->removeFolder($file);
      } else {
      	unlink($file);
      }
    }

    rmdir($folder);
	}

	/**
	 * Set the stub to use
	 *
	 * @return string
	 */
	protected function setStub()
	{
		$this->box->getPhar()->setStub(
			StubGenerator::create()
				->index('bin/rocketeer')
				->generate()
		);
	}

	/**
	 * Add a folder to the PHAR
	 *
	 * @param string $folder
	 * @param array  $ignore
	 *
	 * @return array
	 */
	protected function addFolder($folder, array $ignore = array())
	{
		$finder = new Finder();
		$finder = $finder->files()
			->ignoreVCS(true)
			->name('*.php')
			->in($folder);

		// Ignore some files or folders
		if ($ignore) {
			foreach ($ignore as $file) {
				$finder->exclude($file);
			}
		}

		$this->box->buildFromIterator($finder, dirname($folder));

		return iterator_to_array($finder);
	}
}
