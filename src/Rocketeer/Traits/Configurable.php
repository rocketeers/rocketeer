<?php
namespace Rocketeer\Traits;

trait Configurable
{
    //////////////////////////////////////////////////////////////////////
    //////////////////////// SETTERS AND GETTERS /////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Configure the task
     *
     * @param array $options
     */
    public function configure(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// FETCHING //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a callable option
     *
     * @param string $option
     *
     * @return callable|null
     */
    protected function getClosureOption($option)
    {
        $option = array_get($this->options, $option);
        if (!is_callable($option)) {
            return;
        }

        return $option;
    }
}
