<?php
namespace Rocketeer\TestCases\Modules;

use Closure;

trait Mocks
{
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
        $defaults = array_merge($defaults, array(
                'rocketeer::remote.shell' => false,
                'rocketeer::paths.app'    => $this->app['path.base'],
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
