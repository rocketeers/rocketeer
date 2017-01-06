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

class DummyNotifier extends AbstractNotifier
{
    /**
     * Get the default message format.
     *
     * @param string $message
     *
     * @return string
     */
    public function getMessageFormat($message)
    {
        return '{1} finished deploying branch "{2}" on "{3}" ({4})';
    }

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
}
