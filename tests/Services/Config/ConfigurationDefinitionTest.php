<?php
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
     * Setup the tests
     */
    public function setUp()
    {
        parent::setUp();

        $this->processor = new Processor();
    }

    public function testCanUnifyFlagServerDeclaration()
    {
        $config = [
            'config' => [
                'application_name' => 'foobar',
                'connections'      => [
                    'production' => [
                        'host' => 'foo.com',
                    ],
                ]
            ]
        ];

        $processed  = $this->processor->processConfiguration(new ConfigurationDefinition(), [$config]);
        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
    }

    public function testCanUnifyUnkeyedServerDeclaration()
    {
        $config = [
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
                ]
            ]
        ];

        $processed  = $this->processor->processConfiguration(new ConfigurationDefinition(), [$config]);
        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
        $this->assertEquals('bar.com', $connection['servers'][1]['host']);
    }

    public function testCanUnifyFullServerDeclaration()
    {
        $config = [
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
                        ]
                    ],
                ]
            ]
        ];

        $processed  = $this->processor->processConfiguration(new ConfigurationDefinition(), [$config]);
        $connection = $processed['config']['connections']['production'];

        $this->assertArrayHasKey('servers', $connection);
        $this->assertEquals('foo.com', $connection['servers'][0]['host']);
        $this->assertEquals('bar.com', $connection['servers'][1]['host']);
    }
}
