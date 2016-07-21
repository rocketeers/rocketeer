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

namespace Rocketeer\Dummies\Console;

use Rocketeer\Console\Commands\AbstractCommand;
use Rocketeer\Console\RocketeerStyle;
use Symfony\Component\Console\Input\InputInterface;

class DummyCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $name = 'dummy';

    /**
     * @var array
     */
    protected $answers = [];

    /**
     * @param InputInterface $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * @param RocketeerStyle $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param array $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * Run the tasks.
     *
     * @return mixed
     */
    public function fire()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function confirm($question, $default = true)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function choice($question, array $choices, $default = null)
    {
        return $default ?: head($choices);
    }

    /**
     * {@inheritdoc}
     */
    public function ask($question, $default = null, $validator = null)
    {
        return is_null($default) ? 'foobar' : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askHidden($question, $validator = null)
    {
        return 'foobar';
    }
}
