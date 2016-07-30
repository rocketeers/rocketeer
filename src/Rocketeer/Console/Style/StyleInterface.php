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

namespace Rocketeer\Console\Style;

use Symfony\Component\Console\Style\StyleInterface as SymfonyStyleInterface;

/**
 * Interface for Rocketeer's CLI style.
 */
interface StyleInterface extends SymfonyStyleInterface
{
    /**
     * @param string|array $message
     */
    public function comment($message);
}
