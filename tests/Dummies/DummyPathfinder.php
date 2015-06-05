<?php
namespace Rocketeer\Dummies;

use Rocketeer\Services\Environment\Pathfinders\PathfinderInterface;

class DummyPathfinder implements PathfinderInterface
{
    /**
     * @param string $foo
     *
     * @return string
     */
    public function foobar($foo)
    {
        return $foo.'foo';
    }

    /**
     * The methods this pathfinder provides
     *
     * @return string[]
     */
    public function provides()
    {
        return ['foobar'];
    }
}
