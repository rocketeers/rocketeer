<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Services\Credentials;

use League\Container\ServiceProvider\AbstractServiceProvider;

class CredentialsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'credentials.handler',
        'credentials.gatherer',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->container->share('credentials.handler', function () {
            return new CredentialsHandler($this->container);
        });

        $this->container->share('credentials.gatherer', function () {
            return new CredentialsGatherer($this->container);
        });
    }
}
