<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Compilation;

class RocketeerCompiler
{
    /**
     * @type Compiler
     */
    protected $compiler;

    /**
     * Build a new Rocketeer PHAR compiler
     */
    public function __construct()
    {
        $this->compiler = new Compiler(__DIR__.'/../../../../bin', 'rocketeer', array(
            'd11wtq',
            'herrera-io',
            'johnkary',
            'mockery',
            'nesbot',
            'phine',
        ));

        // Get current commit
        $commit = shell_exec('git rev-parse HEAD');
        $commit = trim($commit);

        $this->compiler->setValues(array(
            '@commit@' => $commit,
        ));
    }

    /**
     * Delegate calls to the Compiler
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->compiler, $name], $arguments);
    }
}
