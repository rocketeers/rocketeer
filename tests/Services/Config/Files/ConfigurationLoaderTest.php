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

namespace Rocketeer\Services\Config\Files;

use Rocketeer\TestCases\RocketeerTestCase;

class ConfigurationLoaderTest extends RocketeerTestCase
{
    public function testCanFilterOutInvalidConfigurationDirectories()
    {
        $this->files->createDir(__DIR__);
        $this->configurationLoader->setFolders([
            'foobar',
            __DIR__,
            __FILE__,
        ]);

        $folders = $this->configurationLoader->getConfigurationFolders();
        $this->assertEquals([__DIR__], $folders);
    }
}
