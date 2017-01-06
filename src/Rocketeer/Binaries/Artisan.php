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

namespace Rocketeer\Binaries;

use Illuminate\Container\Container;
use Rocketeer\Abstracts\AbstractBinary;

class Artisan extends AbstractBinary
{
    /**
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        parent::__construct($app);

        // Set PHP as parent
        $php = new Php($this->app);
        $this->setParent($php);
    }

    /**
     * Get an array of default paths to look for.
     *
     * @return string[]
     */
    protected function getKnownPaths()
    {
        return [
            'artisan',
            $this->releasesManager->getCurrentReleasePath().'/artisan',
        ];
    }

    /**
     * Run outstranding migrations.
     *
     * @return string
     */
    public function migrate()
    {
        return $this->getCommand('migrate', null, ['--force' => null]);
    }

    /**
     * Seed the database.
     *
     * @return string
     */
    public function seed()
    {
        return $this->getCommand('db:seed', null, ['--force' => null]);
    }

    /**
     * Clear the cache.
     *
     * @return string
     */
    public function clearCache()
    {
        return $this->getCommand('cache:clear');
    }
}
