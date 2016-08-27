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

namespace Rocketeer\Services\Ignition;

use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerIgniterTest extends RocketeerTestCase
{
    public function testCanProperlyCreateManifest()
    {
        $path = $this->paths->getRocketeerPath().'/composer.json';
        $this->igniter->exportComposerFile();

        $contents = $this->files->read($path);
        $this->assertEquals(<<<'JSON'
{
    "minimum-stability": "dev",
    "prefer-stable": true,
    "config": {
        "preferred-install": "dist",
        "sort-packages": true
    }
}
JSON
, $contents);
    }

    public function testCanProperlyCreateManifestForNamespace()
    {
        $path = $this->paths->getRocketeerPath().'/composer.json';
        $this->igniter->exportComposerFile('Foobar');

        $contents = $this->files->read($path);
        $this->assertEquals(<<<'JSON'
{
    "minimum-stability": "dev",
    "prefer-stable": true,
    "config": {
        "preferred-install": "dist",
        "sort-packages": true
    },
    "autoload": {
        "psr4": {
            "Foobar\\": "app"
        }
    }
}
JSON
            , $contents);
    }
}
