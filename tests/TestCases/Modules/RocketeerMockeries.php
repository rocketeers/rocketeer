<?php
namespace Rocketeer\TestCases\Modules;

use Closure;

trait RocketeerMockeries
{
    //////////////////////////////////////////////////////////////////////
    //////////////////////////////// STATE ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string|null $system
     */
    protected function mockOperatingSystem($system = null)
    {
        $system = $system ?: PHP_OS;

        $this->localStorage->set('production.os', $system);
        $this->localStorage->set('staging.os', $system);
    }

    /**
     * Mock the Composer check
     *
     * @param boolean $uses
     *
     * @return void
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
     * Set Rocketeer in pretend mode
     *
     * @param array $options
     * @param array $expectations
     */
    protected function pretend($options = array(), $expectations = array())
    {
        $options['pretend'] = true;

        $this->mockCommand($options, $expectations);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// SERVICES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock the ReleasesManager
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
     * Mock a Command
     *
     * @param array $options
     * @param array $expectations
     */
    protected function mockCommand($options = array(), $expectations = array())
    {
        // Default options
        $options = array_merge(array(
            'pretend'  => false,
            'verbose'  => false,
            'tests'    => false,
            'migrate'  => false,
            'seed'     => false,
            'stage'    => false,
            'parallel' => false,
            'update'   => false,
        ), $options);

        $this->app['rocketeer.command'] = $this->getCommand($expectations, $options);
    }

    /**
     * Mock the RemoteHandler
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
     * @param array $expectations
     */
    public function mockConfig(array $expectations)
    {
        $defaults = $this->getFactoryConfiguration();
        $defaults = array_merge($defaults, ['rocketeer::paths.app' => $this->app['path.base']]);

        // Set core expectations
        foreach ($defaults as $key => $value) {
            $this->config->set($key, $value);
        }

        // Set additional expectations
        foreach ($expectations as $key => $value) {
            $this->config->set($key, $value);
        }
    }
}
