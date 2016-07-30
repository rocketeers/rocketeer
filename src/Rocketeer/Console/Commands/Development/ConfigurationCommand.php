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

namespace Rocketeer\Console\Commands\Development;

use Illuminate\Support\Arr;
use Rocketeer\Console\Commands\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Easily dump one or more nodes from the configuration.
 */
class ConfigurationCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'debug:config';

    /**
     * @var string
     */
    protected $description = 'Dumps the current configuration parsed';

    /**
     * {@inheritdoc}
     */
    public function fire()
    {
        $this->prepareEnvironment();

        $key = $this->argument('key');

        $configuration = $this->config->toArray();
        $configuration = $key ? Arr::get($configuration, $key) : $configuration;

        dump($configuration);
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['key', InputArgument::OPTIONAL, 'The key to dump'],
        ];
    }
}
