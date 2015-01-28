<?php
namespace Rocketeer\Interfaces;

interface ConnectionInterface extends \Illuminate\Remote\ConnectionInterface
{
    /**
     * Get the exit status of the last command.
     *
     * @return integer|bool
     */
    public function status();
}
