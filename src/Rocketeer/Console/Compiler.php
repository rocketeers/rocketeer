<?php
namespace Rocketeer\Console;

use Herrera\Box\Box;
use Herrera\Box\StubGenerator;
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

		$this->box = Box::create($phar, 0, basename($phar));

		// Get some paths
		$root   = __DIR__.'/../../..';
		$src    = $root.'/src';
		$vendor = $root.'/vendor';

		// Add core files and dependencies
		$this->addFolder($src);
		$this->addFolder($vendor, array(
			'mockery',
			'patchwork',
			'herrera-io',
		));

		// Add binary
		$binary = file_get_contents($root.'/bin/rocketeer');
		$binary = str_replace('#!/usr/bin/env php', null, $binary);
		$this->box->addFromString('bin/rocketeer', $binary);

		// Set stub
		$this->setStub();
	}

	/**
	 * Get the stub to use
	 *
	 * @return string
	 */
	public function getStub()
	{
		return StubGenerator::create()
			->index('bin/rocketeer')
			->generate();
	}

	/**
	 * Set the stub to use
	 *
	 * @return string
	 */
	protected function setStub()
	{
		$this->box->getPhar()->setStub($this->getStub());
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