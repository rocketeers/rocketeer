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

use Rocketeer\Services\Config\Files\ConfigurationPublisher;
use Rocketeer\Services\Container\Container;

require 'vendor/autoload.php';

// Remove existing configuration
$path = realpath(__DIR__.'/../src/config');
exec('rm -rf '.$path.' && mkdir -p '.$path);

$container = new Container();
$publisher = $container->get(ConfigurationPublisher::class);

/* @var ConfigurationPublisher $publisher */
$publisher->publishNode($path, 'php');
exec('php-cs-fixer fix src/config');
