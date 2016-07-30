<?php
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
    "autoload": {
        "files": [
            "app"
        ]
    },
    "minimum-stability": "dev",
    "prefer-stable": true
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
    "autoload": {
        "psr4": {
            "Foobar\\": "app"
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
JSON
            , $contents);
    }
}
