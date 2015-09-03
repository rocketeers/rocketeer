<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        switch ($message) {
            case 'before_before':
                return '{1} deploying branch "{2}" on "{3}"';
            case 'after_after':
                return '{1} finished deploying branch "{2}" on "{3}"';
            case 'before_deploy':
            case 'after_deploy':
                return '{1} finished deploying {5}/{2} on "{3}" ({4})';

            case 'after_rollback':
                return '{1} rolled back {5}/{2} on "{3}" to previous version ({4})';
        }
    }

    /**
     * Send a given message.
     *
     * @param string $message
     */
    public function send($message)
    {
        print $message;

        return $message;
    }
}
