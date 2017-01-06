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

namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class ClosureTest extends RocketeerTestCase
{
    public function testCanGetDescriptionOfClosureTask()
    {
        $closure = $this->builder->buildTask(['ls', 'ls'], 'FilesLister');

        $this->assertEquals('FilesLister', $closure->getName());
        $this->assertEquals('files-lister', $closure->getSlug());
        $this->assertEquals('ls/ls', $closure->getDescription());
    }
}
