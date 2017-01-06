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

namespace Rocketeer\Dummies;

use Rocketeer\Plugins\AbstractNotifier;

class DummyBeforeAfterNotifier extends AbstractNotifier
{
    /**
     * Send a given message.
     *
     * @param string $message
     */
    public function send($message)
    {
        echo $message;

        return $message;
    }

    /**
     * Get the default message format.
     *
     * @param string $message The message handle
     *
     * @return string
     */
    public function getMessageFormat($message)
    {
        return $message;
    }
}
