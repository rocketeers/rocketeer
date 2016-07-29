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

        $folders = $this->configurationLoader->getFolders();
        $this->assertEquals([__DIR__], $folders);
    }

    /**
     * @dataProvider provideFormats
     *
     * @param string $format
     * @param string $contents
     */
    public function testCanLoadFilesOfVariousFormats($format, $contents)
    {
        $this->files->createDir(__DIR__);
        $this->files->put(__DIR__.'/config.'.$format, $contents);

        $this->configurationLoader->setFolders([__DIR__]);
        $config = $this->configurationLoader->getConfiguration();

        $this->assertEquals($format, $config['config']['application_name']);
    }

    public function provideFormats()
    {
        return [
            ['php', '<?php return ["application_name" => "php"];'],
            ['yml', 'application_name: yml'],
            ['json', '{"application_name": "json"}'],
        ];
    }
}
