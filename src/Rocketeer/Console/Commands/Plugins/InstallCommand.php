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
use Rocketeer\Tasks\Plugins\Installer;

/**
 * Install a plugin.
 */
class InstallCommand extends AbstractPluginCommand
{
    /**
     * @var string
     */
    protected $pluginTask = Installer::class;

    /**
     * The default name.
     *
     * @var string
     */
    protected $name = 'plugins:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a plugin';
}
