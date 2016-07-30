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

namespace Rocketeer\Services\Environment;

use Rocketeer\Traits\ContainerAwareTrait;

/**
 * Gives out various informations about the
 * current server's environment.
 */
class Environment
{
    use ContainerAwareTrait;

    /**
     * Get the directory separators on the remove server.
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->getPhpConstant('directory_separator', 'DIRECTORY_SEPARATOR');
    }

    /**
     * Get the remote line endings on the remove server.
     *
     * @return string
     */
    public function getLineEndings()
    {
        return $this->getPhpConstant('line_endings', 'PHP_EOL');
    }

    /**
     * Get the remote operating system.
     *
     * @return string
     */
    public function getOperatingSystem()
    {
        return $this->getPhpConstant('os', 'PHP_OS');
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Compute the full path to a variable.
     *
     * @param string $variable
     *
     * @return string
     */
    public function getVariablePath($variable)
    {
        return str_replace('/', '.', $this->connections->getCurrentConnectionKey()).'.'.$variable;
    }

    /**
     * Get a cached server variable or compute it.
     *
     * @param string   $variable
     * @param callable $callback
     *
     * @return string
     */
    protected function computeServerVariable($variable, callable $callback)
    {
        $user = $this->config->getContextually('remote.variables.'.$variable);
        if ($user) {
            return $user;
        }

        return $this->localStorage->get($this->getVariablePath($variable), $callback);
    }

    /**
     * Get a PHP constant from the server.
     *
     * @param string $variable
     * @param string $constant
     *
     * @return string
     */
    protected function getPhpConstant($variable, $constant)
    {
        return $this->computeServerVariable($variable, function () use ($variable, $constant) {
            $value = $this->bash->runRaw('php -r "echo '.$constant.';"');
            $this->localStorage->set($this->getVariablePath($variable), $value);

            return $value ?: constant($constant);
        });
    }
}
