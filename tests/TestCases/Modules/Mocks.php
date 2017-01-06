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

namespace Rocketeer\TestCases\Modules;

use Closure;

trait Mocks
{
    //////////////////////////////////////////////////////////////////////
    //////////////////////////////// STATE ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock the Composer check.
     *
     * @param bool $uses
     */
    protected function usesComposer($uses = true)
    {
        $composer = $this->server.'/current/composer.json';
        if ($uses) {
            $this->files->put($composer, '{}');
        } else {
            $this->files->delete($composer);
        }
    }

    /**
     * @param array $state
     */
    protected function mockState(array $state)
    {
        file_put_contents($this->server.'/state.json', json_encode($state));
    }

    /**
     * Set Rocketeer in pretend mode.
     *
     * @param array $options
     * @param array $expectations
     */
    protected function pretend($options = [], $expectations = [])
    {
        $options['pretend'] = true;

        $this->mockCommand($options, $expectations);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// SERVICES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock the ReleasesManager.
     *
     * @param Closure $expectations
     *
     * @return Mockery
     */
    protected function mockReleases(Closure $expectations)
    {
        return $this->mock('rocketeer.releases', 'ReleasesManager', $expectations);
    }

    /**
     * Mock a Command.
     *
     * @param array $options
     * @param array $expectations
     */
    protected function mockCommand($options = [], $expectations = [])
    {
        // Default options
        $options = array_merge([
            'pretend' => false,
            'verbose' => false,
            'tests' => false,
            'migrate' => false,
            'seed' => false,
            'stage' => false,
            'parallel' => false,
            'update' => false,
        ], $options);

        $this->app['rocketeer.command'] = $this->getCommand($expectations, $options);
    }

    /**
     * Mock the RemoteHandler.
     *
     * @param string|array|null $expectations
     */
    protected function mockRemote($expectations = null)
    {
        $this->app['rocketeer.remote'] = $this->getRemote($expectations);
    }

    /**
     * @param Closure|null $expectations
     */
    protected function mockFiles(Closure $expectations = null)
    {
        $this->mock('files', 'Illuminate\Filesystem\Filesystem', $expectations);
    }

    /**
     * @param array $configuration
     */
    public function mockConfig(array $configuration)
    {
        $this->app['config'] = $this->getConfig($configuration);
    }
}
