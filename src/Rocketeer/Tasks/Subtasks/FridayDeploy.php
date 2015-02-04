<?php

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Abstracts\AbstractTask;

/**
 * Provides a motivational message if you're deploying on a friday
 *
 * @author  Bram Devries <bram@madewithlove.be>
 */
class FridayDeploy extends AbstractTask
{
    /**
     * A description of what the task does
     *
     * @type string
     */
    protected $description = 'Wishes you good luck on friday';

    /**
     * Run the task
     */
    public function execute()
    {
        $this->explainer->line('');
        $this->explainer->line('┓┏┓┏┓┃');
        $this->explainer->line('┛┗┛┗┛┃ ＼○／');
        $this->explainer->line('┓┏┓┏┓┃   /     Friday');
        $this->explainer->line('┛┗┛┗┛┃ ノ)');
        $this->explainer->line('┓┏┓┏┓┃         deploy,');
        $this->explainer->line('┛┗┛┗┛┃');
        $this->explainer->line('┓┏┓┏┓┃         good');
        $this->explainer->line('┛┗┛┗┛┃');
        $this->explainer->line('┓┏┓┏┓┃         luck!');
        $this->explainer->line('┃┃┃┃┃┃');
        $this->explainer->line('┻┻┻┻┻┻');
    }
}
