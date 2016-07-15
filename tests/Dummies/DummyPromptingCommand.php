<?php
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
    }
}
