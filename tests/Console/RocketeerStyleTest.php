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

namespace Rocketeer\Console;

use Rocketeer\Dummies\DummyPromptingCommand;
use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerStyleTest extends RocketeerTestCase
{
    public function testDisplaysWarningInNonInteractiveMode()
    {
        $command = new DummyPromptingCommand();
        $tester = $this->executeCommand($command, [], ['interactive' => false]);

        $this->assertContains('prompt was skipped: Annie are you ok', $tester->getDisplay());
    }

    public function testCanReturnDefaultValueInNonInteractiveMode()
    {
        $command = new DummyPromptingCommand();
        $tester = $this->executeCommand($command, [], ['interactive' => false]);

        $this->assertContains('Foo', $tester->getDisplay());
    }
}
