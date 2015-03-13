<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services\Config;

use Rocketeer\TestCases\RocketeerTestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationDefinitionTest extends RocketeerTestCase
{
    /**
     * @type Processor
     */
    protected $processor;

    /**
     * Setup the tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->processor = new Processor();
    }

    public function testCanUnifyFlagServerDeclaration()
    {
        $processed = $this->processConfiguration([
            'config' => [
                'application_name' => 'foobar',
                'connections'      => [
                    'production' => [
                        'host' => 'foo.com',
                    ],
                ],
            ],
        ]);

        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
    }

    public function testCanUnifyUnkeyedServerDeclaration()
    {
        $processed = $this->processConfiguration([
            'config' => [
                'application_name' => 'foobar',
                'connections'      => [
                    'production' => [
                        [
                            'host' => 'foo.com',
                        ],
                        [
                            'host' => 'bar.com',
                        ],
                    ],
                ],
            ],
        ]);

        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
        $this->assertEquals('bar.com', $connection['servers'][1]['host']);
    }

    public function testCanUnifyFullServerDeclaration()
    {
        $processed = $this->processConfiguration([
            'config' => [
                'application_name' => 'foobar',
                'connections'      => [
                    'production' => [
                        'servers' => [
                            [
                                'host' => 'foo.com',
                            ],
                            [
                                'host' => 'bar.com',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
        $this->assertEquals('bar.com', $connection['servers'][1]['host']);
    }

    public function testCanProperlyMergePaths()
    {
        $processed = $this->processor->processConfiguration(new ConfigurationDefinition(), [
            [
                'paths' => [
                    'php'      => '/foo/php',
                    'composer' => '/foo/composer',
                    'foo'      => '/bar',
                ],
            ],
            [
                'paths' => [
                    'php' => '/bar/php',
                    'bar' => '/bar/baz',
                ],
            ],
        ]);

        $this->assertEquals([
            'php'      => '/bar/php',
            'composer' => '/foo/composer',
            'foo'      => '/bar',
            'bar'      => '/bar/baz',
        ], $processed['paths']);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function processConfiguration(array $config)
    {
        return $this->processor->processConfiguration(new ConfigurationDefinition(), [$config]);
    }
}
