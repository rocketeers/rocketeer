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

namespace Rocketeer\Console\Commands\Plugins;

use Rocketeer\Console\Commands\AbstractCommand;

/**
 * Lists the currently enabled plugins.
 */
class ListCommand extends AbstractCommand
{
    /**
     * The default name.
     *
     * @var string
     */
    protected $name = 'plugins:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the currently enabled plugins';

    /**
     * Whether the command's task should be built
     * into a pipeline or run straight.
     *
     * @var bool
     */
    protected $straight = true;

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $rows = [];
        $plugins = $this->container->getPlugins();
        if (!$plugins) {
            return $this->writeln('You currently have no installed plugins');
        }

        foreach ($plugins as $plugin) {
            $rows[] = [$plugin];
        }

        $this->table(['Plugin'], $rows);
    }
}
