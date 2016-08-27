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

namespace Rocketeer\Tasks\Plugins;

/**
 * Updates one or more plugins.
 */
class Updater extends Installer
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates one or more plugins';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        // Get package and destination folder
        $package = $this->getPackage();

        $arguments = $package ? [$package] : null;
        $this->runComposerMethod('update', $arguments);
    }
}
