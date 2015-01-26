<?php
namespace Rocketeer\TestCases\Modules;

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
     * Mock the Composer check
     *
     * @param boolean     $uses
     * @param string|null $stage
     */
    protected function usesComposer($uses = true, $stage = null)
    {
        $composer = $this->server.'/';
        $composer .= $stage ? $stage.'/' : null;
        $composer .= 'current/composer.json';

        // Create directory if necessary
        $folder = dirname($composer);
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

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
}
