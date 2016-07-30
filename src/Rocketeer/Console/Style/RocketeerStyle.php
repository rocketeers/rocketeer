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

namespace Rocketeer\Console\Style;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Defines Rocketeer's CLI output style.
 */
class RocketeerStyle extends SymfonyStyle
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * {@inheritdoc}
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        parent::__construct($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function askQuestion(Question $question)
    {
        if (!$this->input->isInteractive()) {
            $this->writeln('<error>Non-interactive mode, prompt was skipped:</error> '.$question->getQuestion());
            $default = $question instanceof ChoiceQuestion ? head($question->getChoices()) : $question->getDefault();

            return $default;
        }

        return parent::askQuestion($question);
    }
}
