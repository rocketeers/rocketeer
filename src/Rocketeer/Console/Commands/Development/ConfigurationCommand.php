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
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

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

        $configuration = $this->config->all();
        $configuration = $key ? Arr::get($configuration, $key) : $configuration;

        $dumper = new CliDumper();
        $dumper->setColors(true);

        $cloner = new VarCloner();

        $dumper->dump($cloner->cloneVar($configuration), function ($line, $depth) {
            if ($depth !== -1) {
                $this->output->writeln(str_repeat('  ', $depth).$line);
            }
        });
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
