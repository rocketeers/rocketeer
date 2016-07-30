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

namespace Rocketeer\Console\Commands\Ignite;

use Rocketeer\Console\Commands\AbstractCommand;

/**
 * Exports stubs of a certain type.
 */
class StubsCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $name = 'ignite:stubs';

    /**
     * @var string
     */
    protected $description = 'Exports stubs of a certain type';

    /**
     * Run the tasks.
     *
     * @return mixed
     */
    public function fire()
    {
        if (!$this->confirm('This will remove your current "app" folder and composer.json file, do you want to proceed?')) {
            return;
        }

        // Delete previous directory
        $path = $this->paths->getUserlandPath();
        $this->files->deleteDir($path);

        // Export stubs
        $type = $this->choice('Which type of structure do you want?', ['classes', 'functions']);
        $namespace = $type === 'classes' ? $this->bootstrapper->getUserNamespace() : null;
        $this->igniter->exportStubs($type, $path, $namespace);

        $this->success('Stubs exported into "app" folder');
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return [];
    }
}
