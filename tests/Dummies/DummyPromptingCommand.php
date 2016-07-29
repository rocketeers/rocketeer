<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Dummies;

use Rocketeer\Console\Commands\AbstractCommand;

class DummyPromptingCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $name = 'prompting';

    /**
     * Run the tasks.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->command->confirm('Annie are you okay?');
        $this->command->writeln($this->command->choice('Foobar?', ['foo', 'bar']));
    }
}
