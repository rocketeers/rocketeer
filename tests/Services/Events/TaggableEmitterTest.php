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

use Closure;
use Rocketeer\TestCases\RocketeerTestCase;

class TaggableEmitterTest extends RocketeerTestCase
{
    public function testCanTagEvents()
    {
        $this->expectOutputString('bar');

        $emitter = new TaggableEmitter();

        $emitter->setTag('foo');
        $emitter->addListener('foo', $this->echoingListener('foo'));

        $emitter->setTag('bar');
        $emitter->addListener('foo', $this->echoingListener('bar'));

        $emitter->emit('foo');
    }

    public function testCanWrapAllEventsInCallable()
    {
        $this->expectOutputString('foo');

        $emitter = new TaggableEmitter();

        $emitter->setTag('foo');
        $emitter->addListener('foo', $this->echoingListener('foo'));

        $emitter->onTag('bar', function () use ($emitter) {
            $emitter->addListener('foo', $this->echoingListener('bar'));
        });

        $emitter->emit('foo');
        $this->assertEquals('foo', $emitter->getTag());
    }

    public function testStillFiresGlobalEvents()
    {
        $this->expectOutputString('foobar');

        $emitter = new TaggableEmitter();
        $emitter->addListener('foo', $this->echoingListener('foo'));

        $emitter->onTag('bar', function () use ($emitter) {
            $emitter->addListener('foo', $this->echoingListener('bar'));
            $emitter->emit('foo');
        });
    }

    public function testCanClearEventsWithParticularTag()
    {
        $this->expectOutputString('foo');

        $emitter = new TaggableEmitter();
        $emitter->addListener('foo', $this->echoingListener('foo'));
        $emitter->onTag('foo', function () use ($emitter) {
            $emitter->addListener('foo', $this->echoingListener('bar'));

            $emitter->removeListenersWithTag('foo');
            $emitter->emit('foo');
        });
    }

    /**
     * @param string $echo
     *
     * @return Closure
     */
    protected function echoingListener($echo)
    {
        return function () use ($echo) {
            echo $echo;
        };
    }
}
