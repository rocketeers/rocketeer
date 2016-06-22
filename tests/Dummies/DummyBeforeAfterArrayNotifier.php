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

class DummyBeforeAfterArrayNotifier extends AbstractNotifier
{
    /**
     * Send a given message.
     *
     * @param string $message
     */
    public function send($message)
    {
        echo $message['title'];

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
        return array('fallback' => '{1} is deploying "{2}" on "{3}" ({4})',
                     'pretext' => '',
                     'title' => 'dummy_before_after_array_notifier',
                     'title_link' => 'http://example.com',
                     'text' => '{1} is deploying "{2}" on "{3}" ({4})',
                     'color' => 'good');
    }
}
