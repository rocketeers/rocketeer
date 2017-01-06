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

namespace Rocketeer\Abstracts\Strategies;

use Rocketeer\TestCases\RocketeerTestCase;

class AbstractStrategyTest extends RocketeerTestCase
{
    public function testCanCheckForManifestWithoutServer()
    {
        $this->app['path.base'] = realpath(__DIR__.'/../../..');

        $this->usesComposer(false);
        $strategy = $this->builder->buildStrategy('Dependencies', 'Composer');
        $this->assertTrue($strategy->isExecutable());
    }
}
