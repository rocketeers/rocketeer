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

use Closure;
use League\Flysystem\Filesystem;
use Mockery;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\StyleInterface;
use Rocketeer\Services\Connections\ConnectionsFactory;
use Rocketeer\Services\Filesystem\FilesystemInterface;
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
     * Bind a mocked instance in the Container.
     *
     * @param string  $handle
     * @param string  $class
     * @param Closure $expectations
     * @param bool    $partial
     *
     * @return Mockery
     */
    protected function mock($handle, $class = null, Closure $expectations = null, $partial = true)
    {
        $class = $class ?: $handle;
        $mockery = Mockery::mock($class);
        if ($partial) {
            $mockery = $mockery->shouldIgnoreMissing();
        }

        if ($expectations) {
            $mockery = $expectations($mockery)->mock();
        }

        if ($this->container->has($handle)) {
            $this->container->get($handle);
        }

        $this->container->add($handle, $mockery);

        return $mockery;
    }

    /**
     * Mock a Command.
     *
     * @param array $options
     * @param array $expectations
     * @param bool  $print
     */
    protected function mockCommand($options = [], $expectations = [], $print = false)
    {
        // Default options
        $options = array_merge([
            'pretend' => false,
            'verbose' => false,
            'tests' => false,
            'migrate' => false,
            'seed' => false,
            'stage' => false,
            'parallel' => false,
            'update' => false,
        ], $options);

        $this->container->add('rocketeer.command', $this->getCommand($expectations, $options, $print));
    }

    /**
     * Mock a command that echoes out its output.
     */
    protected function mockEchoingCommand()
    {
        $prophecy = $this->bindProphecy(AbstractCommand::class, 'rocketeer.command');
        $prophecy->option(Argument::cetera())->willReturn();
        $prophecy->writeln(Argument::any())->will(function ($arguments) {
            echo $arguments[0];
        });
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
     * @param Closure|null $expectations
     */
    protected function mockFiles(Closure $expectations = null)
    {
        $this->mock(Filesystem::class, Filesystem::class, $expectations);
    }

    /**
     * @param array $expectations
     */
    public function mockConfig(array $expectations)
    {
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
