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

use Rocketeer\Console\Commands\AbstractPluginCommand;
use Rocketeer\Tasks\Plugins\Updater;

class UpdaterCommand extends AbstractPluginCommand
{
    /**
     * @var string
     */
    protected $pluginTask = Updater::class;

    /**
     * The default name.
     *
     * @var string
     */
    protected $name = 'plugin:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update one or all plugin(s)';
}
