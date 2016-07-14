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

namespace Rocketeer\Tasks;

use Rocketeer\Services\Config\ConfigurationDefinition;
use Rocketeer\Services\Config\Files\ConfigurationPublisher;

/**
 * A task to ignite Rocketeer.
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class Ignite extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = "Creates Rocketeer's configuration";

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->command->writeln(<<<'TXT'
                           *     .--.
                                / /  `
               +               | |
                      '         \ \__,
                  *          +   '--'  *
                      +   /\
         +              .'  '.   *
                *      /======\      +
                      ;:.  _   ;
                      |:. (_)  |
                      |:.  _   |
            +         |:. (_)  |          *
                      ;:.      ;
                    .' \:.    / `.
                   / .-'':._.'`-. \
                   |/    /||\    \|
                 _..--"""````"""--.._
           _.-'``                    ``'-._
         -'      WELCOME TO ROCKETEER      '-
TXT
        );

        // Get application name
        $default = basename($this->localStorage->getFilename(), '.json');
        $applicationName = $this->command->ask('What is your application\'s name ?', $default);
        $this->config->set('application_name', $applicationName);

        // Gather repository/connections credentials
        $this->command->title('<info>[1/2]</info> Credentials gathering');
        $this->command->write('Before we begin let\'s gather the credentials for your app');
        $credentials = $this->credentialsGatherer->getCredentials();
        $this->exportDotenv($credentials);

        // Export configuration
        $this->exportConfiguration($applicationName);

        $this->command->writeln('Okay, you are ready to send your projects in the cloud. Fire away rocketeer!');
    }

    /**
     * @param array $credentials
     */
    public function exportDotenv(array $credentials)
    {
        // Build dotenv file
        $dotenv = '';
        foreach ($credentials as $credential => $value) {
            $dotenv .= $credential.'='.$value.PHP_EOL;
        }

        // Write to disk
        $this->files->put(getcwd().'/.env', $dotenv);
        $this->command->writeln('<info>A <comment>.env</comment> file with your credentials has been created!</info>');
        $this->command->writeln('Do not track this file in your repository, <error>it is meant to be private</error>');
    }

    /**
     * Export the configuration to file.
     */
    public function exportConfiguration()
    {
        $this->command->title('<info>[2/2]</info> Configuration exporting');
        $format = $this->command->choice('What format do you want your configuration in?', ConfigurationPublisher::$formats, 'php');
        $consolidated = $this->command->confirm('Do you want it consolidated (one file instead of many?', false);

        // Set values on definition
        $definition = new ConfigurationDefinition();
        $definition->setValues($this->config->all());

        $this->configurationPublisher->setDefinition($definition);
        $path = $this->configurationPublisher->publish($format, $consolidated);

        // Summary
        $folder = basename(dirname($path)).'/'.basename($path);
        $this->command->writeln('<info>Your configuration was exported at</info> <comment>'.$folder.'</comment>.');
        $this->command->writeln('Go take a look at it now and update everything that needs updatin\'');
    }
}
