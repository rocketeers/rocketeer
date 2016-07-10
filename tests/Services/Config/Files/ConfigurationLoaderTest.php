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
use Symfony\Component\Finder\Finder;

class ConfigurationLoaderTest extends RocketeerTestCase
{
    public function testCanCacheConfigurationAndInvalidateIt()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('It\'s ok I hate you too HHVM');
        }

        $folder = $this->replicateConfiguration();
        $filepath = $folder.'/config.php';
        $originalContents = file_get_contents($filepath);

        // Check that we can cache configuration
        $config = $this->configurationLoader->getConfiguration();
        $this->assertEquals('{application_name}', $config['config']['application_name']);
        touch($filepath, time() - 5);
        $this->assertTrue($this->configurationLoader->getCache()->isFresh());

        // Check that we can invalidate it
        file_put_contents($filepath, str_replace('{application_name}', 'foobar', $originalContents));
        $this->assertFalse($this->configurationLoader->getCache()->isFresh());

        $config = $this->configurationLoader->getConfiguration();
        touch($filepath, time() - 5);
        $this->assertTrue($this->configurationLoader->getCache()->isFresh());
        $this->assertEquals('foobar', $config['config']['application_name']);

        // Revert changes
        file_put_contents($filepath, $originalContents);
    }

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

    /**
     * Replicates the configuration onto the VFS.
     */
    protected function replicateConfiguration()
    {
        $folder = $this->configurationLoader->getFolders()[0];
        $this->files->createDir($folder);

        $files = (new Finder())->in($folder)->files();
        foreach ($files as $file) {
            $contents = file_get_contents($file->getPathname());
            $this->files->write($folder.'/'.$file->getBasename(), $contents);
        }

        $this->configurationLoader->setFolders([$folder]);
        $this->configurationLoader->getCache()->flush();

        return $folder;
    }
}
