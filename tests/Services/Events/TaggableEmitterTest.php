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

namespace Rocketeer\Services\Events;

use Rocketeer\TestCases\RocketeerTestCase;

class TaggableEmitterTest extends RocketeerTestCase
{
    public function testCanTagEvents()
    {
        $this->expectOutputString('bar');

        $emitter = new TaggableEmitter();

        $emitter->setTag('foo');
        $emitter->addListener('foo', function () {
            echo 'foo';
        });

        $emitter->setTag('bar');
        $emitter->addListener('foo', function () {
            echo 'bar';
        });

        $emitter->emit('foo');
    }

    public function testCanWrapAllEventsInCallable()
    {
        $this->expectOutputString('foo');

        $emitter = new TaggableEmitter();

        $emitter->setTag('foo');
        $emitter->addListener('foo', function () {
            echo 'foo';
        });

        $emitter->onTag('bar', function () use ($emitter) {
            $emitter->addListener('foo', function () {
                echo 'bar';
            });
        });

        $emitter->emit('foo');
        $this->assertEquals('foo', $emitter->getTag());
    }
}
