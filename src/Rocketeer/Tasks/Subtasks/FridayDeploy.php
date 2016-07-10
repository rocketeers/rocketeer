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

namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\Tasks\AbstractTask;

/**
 * Provides a motivational message if you're deploying on a friday.
 *
 * @author  Bram Devries <bram@madewithlove.be>
 */
class FridayDeploy extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Wishes you good luck on friday';

    /**
     * {@inheritdoc}
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
