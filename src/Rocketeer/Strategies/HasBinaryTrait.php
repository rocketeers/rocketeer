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

namespace Rocketeer\Strategies;

use Rocketeer\Binaries\AbstractBinary;

trait HasBinaryTrait
{
    /**
     * @var string|AbstractBinary
     */
    protected $binary;

    /**
     * @return AbstractBinary
     */
    public function getBinary()
    {
        if (is_string($this->binary)) {
            $this->binary = $this->binary($this->binary);
        }

        return $this->binary;
    }

    /**
     * @param string $manager
     */
    public function setBinary($manager)
    {
        $this->binary = $manager;
    }
}
