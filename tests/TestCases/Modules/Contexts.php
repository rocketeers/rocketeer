<?php
namespace Rocketeer\TestCases\Modules;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Contexts
{
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

    /**
     * Swap the current config
     *
     * @param array $config
     *
     * @return void
     */
    protected function swapConfig($config = [])
    {
        $this->connections->disconnect();
        $this->mockConfig($config);
        $this->tasks->registerConfiguredEvents();
    }

    /**
     * Disable the test events
     */
    protected function disableTestEvents()
    {
        $this->swapConfig(array(
            'rocketeer::hooks' => array(),
        ));
    }

    /**
     * Swap the configured connections
     *
     * @param array $connections
     */
    protected function swapConnections(array $connections)
    {
        $this->swapConfig(array(
            'rocketeer::connections' => $connections,
        ));
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// PACKAGE MANAGERS //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock usage of Laravel framework
     *
     * @param boolean $uses
     */
    protected function usesLaravel($uses = true)
    {
        if ($uses) {
            $this->tasks->plugin('Rocketeer\Plugins\Laravel\LaravelPlugin');
        } else {
            unset($this->app['rocketeer.strategies.framework']);
        }
    }

    /**
     * Mock the Composer check
     *
     * @param boolean     $uses
     * @param string|null $stage
     */
    protected function usesComposer($uses = true, $stage = null)
    {
        $this->mockPackageManagerUsage($uses, 'composer.json', $stage, '{}');
    }

    /**
     * Mock the Bundler check
     *
     * @param boolean     $uses
     * @param string|null $stage
     */
    protected function usesBundler($uses = true, $stage = null)
    {
        $this->mockPackageManagerUsage($uses, 'Gemfile', $stage);
    }

    /**
     * Mock the use of a package manager
     *
     * @param boolean     $uses
     * @param string      $filename
     * @param string|null $stage
     * @param string|null $contents
     */
    protected function mockPackageManagerUsage($uses, $filename, $stage = null, $contents = null)
    {
        $manifest = $this->server.'/';
        $manifest .= $stage ? $stage.'/' : null;
        $manifest .= 'releases/20000000000000/'.$filename;

        // Create directory if necessary
        $folder = dirname($manifest);
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if ($uses) {
            $this->files->put($manifest, $contents);
        } else {
            $this->files->delete($manifest);
        }
    }
}
