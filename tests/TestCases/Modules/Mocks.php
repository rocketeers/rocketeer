<?php
namespace Rocketeer\TestCases\Modules;

use Closure;
use Mockery;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Mocks
{
    /**
     * Bind a mocked instance in the Container.
     *
     * @param string  $handle
     * @param string  $class
     * @param Closure $expectations
     * @param boolean $partial
     *
     * @return Mockery
     */
    protected function mock($handle, $class = null, Closure $expectations = null, $partial = true)
    {
        $class   = $class ?: $handle;
        $mockery = Mockery::mock($class);
        if ($partial) {
            $mockery = $mockery->shouldIgnoreMissing();
        }

        if ($expectations) {
            $mockery = $expectations($mockery)->mock();
        }

        $this->app[$handle] = $mockery;

        return $mockery;
    }

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
     * @param bool  $print
     */
    protected function mockCommand($options = array(), $expectations = array(), $print = false)
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

        $this->app['rocketeer.command'] = $this->getCommand($expectations, $options, $print);
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
     * @param array $expectations
     */
    public function mockConfig(array $expectations)
    {
        $defaults = $this->getFactoryConfiguration();
        $defaults = array_merge($defaults, array(
                'remote.shell' => false,
                'paths.app'    => $this->app['path.base'],
            )
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
}
