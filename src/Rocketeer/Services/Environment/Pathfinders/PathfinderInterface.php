<?php
namespace Rocketeer\Services\Environment\Pathfinders;

interface PathfinderInterface
{
    /**
     * The methods this pathfinder provides
     *
     * @return string[]
     */
    public function provides();
}
