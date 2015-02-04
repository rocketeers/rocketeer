<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console\Compilation;

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
     * @type Box
     */
    protected $box;

    /**
     * The Filesystem instance
     *
     * @type Filesystem
     */
    protected $files;

    /**
     * The path where the PHAR
     * will go when compiled
     *
     * @type string
     */
    protected $folder;

    /**
     * The path to the final phar
     *
     * @type string
     */
    protected $phar;

    /**
     * Path to the binaries folder
     *
     * @type string
     */
    protected $binary;

    /**
     * Name of the PHAR
     *
     * @type string
     */
    protected $name;

    /**
     * Dependencies to exclude
     *
     * @type array
     */
    protected $dependencies;

    /**
     * Values to replace
     *
     * @type array
     */
    protected $values;

    /**
     * Build a new Compiler instance
     *
     * @param string   $folder
     * @param string   $name
     * @param string[] $dependencies
     */
    public function __construct($folder, $name, $dependencies = [])
    {
        $this->files = new Filesystem();
        $this->phar  = $folder.'/'.$name.'.phar';

        $this->folder       = $folder;
        $this->name         = $name;
        $this->dependencies = $dependencies;
    }

    /**
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * Extract an existing Phar
     *
     * @param string $destination
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
        $root   = $this->folder.'/..';
        $src    = $root.'/src';
        $vendor = $root.'/vendor';

        // Create Box
        $this->box = Box::create($this->phar, 0, basename($this->phar));

        // Replace values
        if ($this->values) {
            $this->box->setValues($this->values);
        }

        // Add compactors
        $this->box->addCompactor(new WhitespaceCompactor());

        // Add core files and dependencies
        $this->addFolder($src);
        $this->addFolder($vendor, $this->dependencies);

        // Add binary
        $binary = file_get_contents($root.'/bin/'.$this->name);
        $binary = str_replace('#!/usr/bin/env php', null, $binary);
        $this->box->addFromString('bin/'.$this->name, $binary);

        // Set stub
        $this->setStub();

        return $this->phar;
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// HELPERS ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Set the stub to use
     */
    protected function setStub()
    {
        $stub = StubGenerator::create()->index('bin/'.$this->name)->generate();

        $this->box->getPhar()->setStub($stub);
    }

    /**
     * Add a folder to the PHAR
     *
     * @param string   $folder
     * @param string[] $ignore
     *
     * @return string[]
     */
    protected function addFolder($folder, array $ignore = array())
    {
        $finder = new Finder();
        $finder = $finder->files()->ignoreVCS(true)->name('/\.(php|exe)/')->in($folder);

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
