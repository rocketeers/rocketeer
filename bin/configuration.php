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

use Rocketeer\Container;
use Rocketeer\Services\Config\Files\ConfigurationPublisher;

require 'vendor/autoload.php';

$container = new Container();
$publisher = $container->get(ConfigurationPublisher::class);

// Remove existing configuration
exec('rm -rf src/config && mkdir -p src/config');

/** @var ConfigurationPublisher $publisher */
$publisher->publishNode(__DIR__.'/../src/config', 'php');
exec('php-cs-fixer fix src/config');
