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

namespace Rocketeer\TestCases\Modules\Mocks;

use Rocketeer\Dummies\Tasks\MyCustomTask;

trait ConfigurationTester
{
    /**
     * @var array
     */
    protected $defaults;

    /**
     * @return array
     */
    protected function getFactoryConfiguration()
    {
        if ($this->defaults) {
            return $this->defaults;
        }

        // Base the mocked configuration off the factory values
        $defaults = [];
        $files = ['config', 'hooks', 'paths', 'remote', 'vcs', 'stages', 'strategies'];
        foreach ($files as $file) {
            $defaults[$file] = $this->config->get($file);
        }

        // Build correct keys
        $defaults = array_dot($defaults);
        $keys = array_keys($defaults);
        $keys = array_map(function ($key) {
            return str_replace('config.', null, $key);
        }, $keys);
        $defaults = array_combine($keys, array_values($defaults));

        $overrides = [
            'cache.driver' => 'file',
            'database.default' => 'mysql',
            'default' => 'production',
            'session.driver' => 'file',
            'connections' => [
                'production' => [
                    'host' => '{host}',
                    'username' => '{username}',
                    'password' => '{password}',
                    'root_directory' => dirname($this->server),
                    'roles' => ['web', 'db'],
                ],
                'staging' => [
                    'host' => '{host}',
                    'username' => '{username}',
                    'password' => '{password}',
                    'root_directory' => dirname($this->server),
                    'roles' => ['web', 'db'],
                ],
            ],
            'application_name' => 'foobar',
            'logs' => null,
            'remote.permissions.files' => ['tests'],
            'remote.shared' => ['tests/Elements'],
            'remote.keep_releases' => 1,
            'vcs' => [
                'vcs' => 'git',
                'branch' => 'master',
                'repository' => 'https://github.com/'.$this->repository,
                'shallow' => true,
                'submodules' => true,
            ],
            'strategies.dependencies' => 'Composer',
            'hooks' => [
                'tasks' => [MyCustomTask::class],
                'events' => [
                    'before' => [
                        'deploy' => [
                            'before',
                            'foobar',
                        ],
                    ],
                    'after' => [
                        'check' => [
                            MyCustomTask::class,
                        ],
                        'deploy' => [
                            'after',
                            'foobar',
                        ],
                    ],
                ],
            ],
        ];

        // Assign options to expectations
        $this->defaults = array_merge($defaults, $overrides);

        return $this->defaults;
    }

    /**
     * @param array $expectations
     */
    public function swapConfig(array $expectations = [])
    {
        $this->connections->disconnect();

        $defaults = $this->getFactoryConfiguration();
        $defaults = array_merge($defaults, [
                'remote.shell' => false,
                'paths.app' => $this->container->get('path.base'),
            ]
        );

        // Set core expectations
        foreach ($defaults as $key => $value) {
            $this->config->set($key, $value);
        }

        // Set additional expectations
        foreach ($expectations as $key => $value) {
            $this->config->set($key, $value);
        }
    }

    /**
     * Swap the current config.
     *
     * @param array $config
     */
    protected function swapConfigWithEvents($config = [])
    {
        $this->swapConfig($config);
        $this->bootstrapper->bootstrapUserCode();
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////// CREDENTIALS //////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Swap the configured connections.
     *
     * @param array $connections
     */
    protected function swapConnections(array $connections)
    {
        // Merge defaults to connections
        foreach ($connections as $key => $connection) {
            $connections[$key] = array_merge([
                'root_directory' => dirname($this->server),
            ], $connection);
        }

        $this->swapConfig([
            'connections' => $connections,
        ]);
    }

    /**
     * @param array $credentials
     */
    protected function swapVcsConfiguration(array $credentials)
    {
        $vcs = $this->config->get('vcs');
        $vcs = array_merge($vcs, $credentials);

        $this->config->set('vcs', $vcs);
    }

    /**
     * Make the config return specific VCS config.
     *
     * @param string      $repository
     * @param string|null $username
     * @param string|null $password
     */
    protected function swapRepositoryConfiguration($repository, $username = null, $password = null)
    {
        $this->swapVcsConfiguration([
            'repository' => $repository,
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Disable the test events.
     */
    protected function disableTestEvents()
    {
        $this->swapConfigWithEvents([
            'hooks' => [],
        ]);
    }
}
