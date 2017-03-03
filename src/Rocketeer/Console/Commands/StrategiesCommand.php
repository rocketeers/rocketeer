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

namespace Rocketeer\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Lists the available options for each strategy.
 */
class StrategiesCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'strategies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the available options for each strategy';

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $strategies = [
            'Check' => ['Node', 'Php', 'Ruby', 'Polyglot'],
            'CreateRelease' => ['Clone', 'Copy'],
            'Dependencies' => ['Bower', 'Bundler', 'Composer', 'Npm', 'Polyglot'],
            'Deploy' => ['Rolling', 'Sync', 'Upload'],
            'Rollback' => ['Rolling'],
            'Migrate' => ['Artisan'],
            'Test' => ['Phpunit'],
        ];

        // Return a single task if needed
        $task = mb_strtolower($this->argument('task'));
        if ($task && isset($strategies[$task])) {
            $strategies = [$task => $strategies[$task]];
        }

        $rows = [];
        foreach ($strategies as $strategy => $implementations) {
            foreach ($implementations as $implementation) {
                $instance = $this->builder->buildStrategy($strategy, $implementation);
                if ($instance) {
                    $rows[] = [$strategy, $implementation, $instance->getDescription()];
                }
            }
        }

        $this->table(['Strategy', 'Implementation', 'Description'], $rows);
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['task', InputArgument::OPTIONAL, 'The task to check strategies for'],
        ]);
    }
}
