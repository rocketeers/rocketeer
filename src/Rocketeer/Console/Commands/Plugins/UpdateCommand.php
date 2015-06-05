<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console\Commands\Plugins;

use Rocketeer\Abstracts\Commands\AbstractPluginCommand;

class UpdateCommand extends AbstractPluginCommand
{
    /**
     * @var string
     */
    protected $pluginTask = 'Updater';

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
