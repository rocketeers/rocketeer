<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Container\Container;
use Rocketeer\RocketeerServiceProvider;

require 'vendor/autoload.php';

$container = new Container();
$provider = new RocketeerServiceProvider($container);
$provider->register();

$container['rocketeer.config.publisher']->publish(__DIR__.'/../src/config', 'php');
