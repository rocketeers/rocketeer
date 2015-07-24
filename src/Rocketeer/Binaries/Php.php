<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Binaries;

use Rocketeer\Abstracts\AbstractBinary;

class Php extends AbstractBinary
{
    /**
     * Get an array of default paths to look for.
     *
     * @return string[]
     */
    protected function getKnownPaths()
    {
        return ['php'];
    }

    /**
     * Get the running version of PHP.
     *
     * @return string
     */
    public function version()
    {
        $constant = $this->isHhvm() ? 'HHVM_VERSION' : 'PHP_VERSION';

        return $this->getCommand(null, null, '-r "print '.$constant.';"');
    }

    /**
     * Get the installed extensions.
     *
     * @return string
     */
    public function extensions()
    {
        return $this->getCommand(null, null, ['-m' => null]);
    }

    /**
     * Whether this PHP installation is an HHVM one or not.
     *
     * @return bool
     */
    public function isHhvm()
    {
        $isHhvm = $this->getCommand(null, null, '-r "print defined(\'HHVM_VERSION\');"');
        $isHhvm = $this->bash->runRaw($isHhvm);
        $isHhvm = $isHhvm === '1';

        return $isHhvm;
    }
}
