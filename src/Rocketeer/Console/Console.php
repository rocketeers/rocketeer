<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocketeer\Console;

use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Container;
use Rocketeer\Rocketeer;
use Rocketeer\Traits\HasLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * A standalone Rocketeer CLI.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Console extends Application
{
    use HasLocator;

    /**
     * Create a new Artisan console application.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;

        parent::__construct('Rocketeer');
    }

    /**
     * {@inheritdoc}
     */
    public function add(Command $command)
    {
        if ($command instanceof AbstractCommand) {
            $command->setContainer($this->app);
        }

        return parent::add($command);
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////////// HELP ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Display the application's help.
     *
     * @return string
     */
    public function getHelp()
    {
        $help = str_replace($this->getLongVersion(), null, parent::getHelp());
        $state = $this->buildBlock('Current state', $this->getCurrentState());
        $help = sprintf('%s'.PHP_EOL.PHP_EOL.'%s%s', $this->getLongVersion(), $state, $help);

        return $help;
    }

    /**
     * @return string
     */
    public function getLongVersion()
    {
        $version = Rocketeer::COMMIT === '@commit@' ? '(dev version)' : Rocketeer::COMMIT;

        return sprintf(
            '<info>%s</info> <comment>%s</comment>',
            $this->getName(),
            $version
        );
    }

    /**
     * Build an help block.
     *
     * @param string   $title
     * @param string[] $informations
     *
     * @return string
     */
    protected function buildBlock($title, $informations)
    {
        $message = '<comment>'.$title.'</comment>';
        foreach ($informations as $name => $info) {
            $message .= PHP_EOL.sprintf('  <info>%-15s</info> %s', $name, $info);
        }

        return $message;
    }

    /**
     * Get current state of the CLI.
     *
     * @return string[]
     */
    protected function getCurrentState()
    {
        return [
            'application_name' => realpath($this->app->get('config')->get('application_name')),
            'application' => realpath($this->app->get('paths')->getApplicationPath()),
            'configuration' => realpath($this->app->get('paths')->getConfigurationPath()),
            'tasks' => $this->app->get('path.rocketeer.tasks'),
            'events' => $this->app->get('path.rocketeer.events'),
            'logs' => $this->app->get('path.rocketeer.logs'),
        ];
    }
}
