<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Strategies\Check;

/**
 * Checks if the server is ready to receive a PHP application.
 */
class PhpStrategy extends AbstractCheckStrategy implements CheckStrategyInterface
{
    /**
     * The language of the strategy.
     *
     * @var string
     */
    protected $language = 'PHP';

    /**
     * @var string
     */
    protected $binary = 'php';

    /**
     * @var string
     */
    protected $manager = 'composer';

    /**
     * @var string
     */
    protected $description = 'Checks if the server is ready to receive a PHP application';

    /**
     * The PHP extensions loaded on server.
     *
     * @var array
     */
    protected $extensions = [];

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// CHECKS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the version constraint which should be checked against.
     *
     * @param string $manifest
     *
     * @return string
     */
    protected function getLanguageConstraint($manifest)
    {
        return $this->getLanguageConstraintFromJson($manifest, 'require.php');
    }

    /**
     * Get the current version in use.
     *
     * @return string
     */
    protected function getCurrentVersion()
    {
        return $this->getBinary()->runLast('version');
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// EXTENSIONS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Check for the required extensions.
     *
     * @return string[]
     */
    public function getRequiredExtensions()
    {
        $extensions = [];
        if (!$manifest = $this->getManager()->getManifestContents()) {
            return $extensions;
        }

        $data = json_decode($manifest, true);
        $require = (array) array_get($data, 'require');
        foreach ($require as $package => $version) {
            if (mb_substr($package, 0, 4) === 'ext-') {
                $extensions[] = mb_substr($package, 4);
            }
        }

        return $extensions;
    }

    /**
     * Check the presence of a PHP extension.
     *
     * @param string $extension The extension
     *
     * @return bool
     */
    public function checkExtension($extension)
    {
        // Check for HHVM and built-in extensions
        if ($this->php()->isHhvm()) {
            $this->extensions = [
                '_hhvm',
                'apache',
                'asio',
                'bcmath',
                'bz2',
                'ctype',
                'curl',
                'debugger',
                'fileinfo',
                'filter',
                'gd',
                'hash',
                'hh',
                'iconv',
                'icu',
                'imagick',
                'imap',
                'json',
                'mailparse',
                'mcrypt',
                'memcache',
                'memcached',
                'mysql',
                'odbc',
                'openssl',
                'pcre',
                'phar',
                'reflection',
                'session',
                'soap',
                'std',
                'stream',
                'thrift',
                'url',
                'wddx',
                'xdebug',
                'zip',
                'zlib',
            ];
        }

        // Get the PHP extensions available
        if (!$this->extensions) {
            $this->extensions = (array) $this->bash->run($this->php()->extensions(), false, true);
        }

        return in_array($extension, $this->extensions, true);
    }
}
