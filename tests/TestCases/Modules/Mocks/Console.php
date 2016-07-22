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

namespace Rocketeer\TestCases\Modules\Mocks;

use Prophecy\Argument;
use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Dummies\Console\DummyCommand;
use Rocketeer\TestCases\Modules\Command;
use Rocketeer\TestCases\Modules\ObjectProphecy;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

trait Console
{
    /**
     * Get and execute a command.
     *
     * @param Command|string|null $command
     * @param array               $arguments
     * @param array               $options
     *
     * @return CommandTester
     */
    protected function executeCommand($command = null, $arguments = [], $options = [])
    {
        $command = $this->command($command);

        // Execute
        $tester = new CommandTester($command);
        $tester->execute($arguments, $options + ['interactive' => false]);

        return $tester;
    }

    /**
     * Set Rocketeer in pretend mode.
     *
     * @param array $options
     *
     * @internal param array $expectations
     */
    protected function pretend($options = [])
    {
        $options = array_merge(['--pretend' => true], (array) $options);

        $this->bindDummyCommand($options);
    }

    /**
     * Get a command instance.
     *
     * @param string|Command $command
     *
     * @return Command
     */
    protected function command($command)
    {
        // Fetch command from Container if necessary
        if (!$command instanceof AbstractCommand) {
            $command = $command ? $command : null;
            $command = $this->console->get($command);
        } elseif (!$command->getContainer()) {
            $command->setContainer($this->container);
            $command->setHelperSet(new HelperSet([
                'question' => new QuestionHelper(),
            ]));
        }

        return $command;
    }

    /**
     * Mock a Command.
     *
     * @param array $input
     */
    protected function bindDummyCommand($input = [])
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
