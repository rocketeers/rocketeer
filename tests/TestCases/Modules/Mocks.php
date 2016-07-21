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

namespace Rocketeer\TestCases\Modules;

use League\Flysystem\Filesystem;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\StyleInterface;
use Rocketeer\Dummies\Console\DummyCommand;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Filesystem\FilesystemInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Mocks
{
    /**
     * @param string|ObjectProphecy $class
     * @param string|null           $handle
     *
     * @return ObjectProphecy
     */
    protected function bindProphecy($class, $handle = null)
    {
        $prophecy = $class instanceof ObjectProphecy ? $class : $this->prophesize($class);
        switch ($class) {
            case Filesystem::class:
                $prophecy->willImplement(FilesystemInterface::class);
                break;
            case AbstractCommand::class:
                $prophecy
                    ->willImplement(StyleInterface::class)
                    ->willImplement(OutputInterface::class);
                break;
        }

        $handle = $handle ?: $class;

        if ($this->container->has($handle)) {
            $this->container->get($handle);
        }

        $this->container->add($handle, $prophecy->reveal());

        return $prophecy;
    }

    /**
     * Mock a Command.
     *
     * @param array $input
     */
    protected function mockCommand($input = [])
    {
        // Default options
        $input = array_merge([
            '--branch' => '',
            '--host' => '',
            '--key' => '',
            '--keyphrase' => '',
            '--list' => false,
            '--migrate' => false,
            '--no-clear' => false,
            '--parallel' => false,
            '--pretend' => false,
            '--release' => '',
            '--repository' => '',
            '--root' => '',
            '--seed' => false,
            '--server' => '',
            '--stage' => false,
            '--tests' => false,
            '--update' => false,
            '--username' => '',
            '--verbose' => false,
            'package' => '',
            'release' => '',
        ], $input);

        $definition = new InputDefinition();
        foreach ($input as $key => $option) {
            $isOption = strpos($key, '--') !== false;
            if ($isOption) {
                $definition->addOption(new InputOption(substr($key, 2)));
            } else {
                $definition->addArgument(new InputArgument($key));
            }
        }

        $input = new ArrayInput($input, $definition);
        $input->setInteractive(true);

        $command = new DummyCommand();
        $command->setInput($input);
        $command->setOutput(new NullOutput());

        $this->container->add('rocketeer.command', $command);
    }

    /**
     * Mock a command that echoes out its output.
     *
     * @return AbstractCommand
     */
    protected function mockEchoingCommand()
    {
        $prophecy = $this->bindProphecy(AbstractCommand::class, 'rocketeer.command');
        $prophecy->option(Argument::cetera())->willReturn();
        $prophecy->writeln(Argument::any())->will(function ($arguments) {
            echo $arguments[0];
        });

        return $prophecy;
    }

    /**
     * Mock the RemoteHandler.
     *
     * @param string|array|null $expectations
     */
    protected function mockRemote($expectations = null)
    {
        $this->container->add(ConnectionsFactory::class, $this->getConnectionsFactory($expectations));
        $this->connections->disconnect();
    }

    /**
     * @param bool $withAdapter
     *
     * @return Filesystem
     */
    protected function bindFilesystemProphecy($withAdapter = false)
    {
        $adapter = $this->files->getAdapter();
        $prophecy = $this->bindProphecy(Filesystem::class);
        if ($withAdapter) {
            $prophecy->getAdapter()->willReturn($adapter);
        }

        return $prophecy;
    }

    /**
     * @param array $expectations
     */
    public function mockConfig(array $expectations)
    {
        $this->connections->disconnect();
        $defaults = $this->getFactoryConfiguration();
        $defaults = array_merge($defaults, [
                'remote.shell' => false,
                'paths.app' => $this->container->get('path.base'),
            ]
        );

        // Set core expectations
        foreach ($defaults as $key => $value) {
            $this->config->set($key, $value);
        }

        // Set additional expectations
        foreach ($expectations as $key => $value) {
            $this->config->set($key, $value);
        }
    }

    /**
     * Mock a set of question/answers.
     *
     * @param array $answers
     *
     * @return ObjectProphecy
     */
    protected function mockAnswers(array $answers = [])
    {
        $prophecy = $this->bindProphecy(AbstractCommand::class, 'rocketeer.command');

        if (!$answers) {
            $prophecy->ask(Argument::any())->shouldNotBeCalled();
        }

        $prophecy->getVerbosity()->willReturn(OutputInterface::OUTPUT_NORMAL);
        $prophecy->writeln(Argument::cetera())->willReturn();
        $prophecy->text(Argument::cetera())->willReturn();
        $prophecy->table(Argument::cetera())->willReturn();
        $prophecy->title(Argument::cetera())->willReturn();
        $prophecy->option(Argument::cetera())->willReturn();
        $prophecy->ask(Argument::cetera())->willReturn();
        $prophecy->askHidden(Argument::cetera())->willReturn();
        $prophecy->confirm(Argument::cetera())->willReturn();
        $prophecy->choice(Argument::cetera())->willReturnArgument(2);

        foreach ($answers as $question => $answer) {
            $argument = Argument::containingString($question);

            $prophecy->ask($argument, Argument::any())->willReturn($answer);
            $prophecy->askHidden($argument, Argument::any())->willReturn($answer);
            $prophecy->confirm($argument, Argument::any())->willReturn($answer);
        }

        $this->container->add('rocketeer.command', $prophecy->reveal());

        return $prophecy;
    }
}
