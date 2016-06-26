<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console\Commands;

/**
 * Flushes any custom storage Rocketeer has created.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class FlushCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Flushes Rocketeer's cache of credentials";

    /**
     * Execute the tasks.
     *
     * @return int
     */
    public function fire()
    {
        $this->app->instance('rocketeer.command', $this);

        // Clear the cache of credentials
        $this->configurationLoader->flushCache();
        $this->localStorage->destroy();
        $this->explainer->info("Rocketeer's cache has been properly flushed");

        return 0;
    }
}
