<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console;

use Herrera\Box\Box;
use Herrera\Box\StubGenerator;
use Illuminate\Filesystem\Filesystem;
use Phar;
use Symfony\Component\Finder\Finder;

/**
 * Compiles Rocketeer into a PHAR archive
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Compiler
{
	/**
	 * The current Box instance
	 *
	 * @var Box
	 */
	protected $box;

	/**
	 * The Filesystem instance
	 *
	 * @var Filesystem
	 */
	protected $files;

	/**
	 * The path to the final phar
	 *
	 * @var string
	 */
	protected $phar;

	/**
	 * Build a new Compiler instance
	 */
	public function __construct()
	{
		$this->files = new Filesystem;
		$this->phar  = __DIR__.'/../../../bin/rocketeer.phar';
	}

	/**
	 * Extract an existing Phar
	 *
	 * @param string $destination
	 *
	 * @return void
	 */
	public function extract($destination)
	{
		// Recompile phar archive
		$this->compile();

		// Remove any already extracted archive
		if (file_exists($destination)) {
			$this->files->deleteDirectory($destination);
		}

		$phar = new Phar($this->phar);
		$phar->extractTo($destination);
	}

	/**
	 * Compile the final PHAR
	 *
	 * @return string Path to the created phar
	 */
	public function compile()
	{
		// Remove existing PHAR
		if (file_exists($this->phar)) {
			unlink($this->phar);
		}

		// Store some path variables
		$root   = __DIR__.'/../../..';
		$src    = $root.'/src';
		$vendor = $root.'/vendor';

		// Create Box
		$this->box = Box::create($this->phar, 0, basename($this->phar));

		// Add compactors
		$this->box->addCompactor(new WhitespaceCompactor);

		// Add core files and dependencies
		$this->addFolder($src);
		$this->addFolder($vendor, array(
			'mockery',
			'patchwork',
			'herrera-io',
			'nesbot',
			'phine',
		));

		// Add binary
		$binary = file_get_contents($root.'/bin/rocketeer');
		$binary = str_replace('#!/usr/bin/env php', null, $binary);
		$this->box->addFromString('bin/rocketeer', $binary);

		// Set stub
		$this->setStub();

		return $this->phar;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

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
