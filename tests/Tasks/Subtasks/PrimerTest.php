<?php
namespace Rocketeer\Tasks\Subtasks;

use Rocketeer\TestCases\RocketeerTestCase;

class PrimerTest extends RocketeerTestCase
{
    public function testCanExecutePrimerTasks()
    {
        $this->swapConfig(array(
            'rocketeer::default'           => 'production',
            'rocketeer::strategies.primer' => function () {
                return 'ls';
            },
        ));

        $this->assertTaskHistory('Primer', ['ls']);
    }

    public function testIsRunBeforeDeployCommand()
    {
        $this->rocketeer->setLocal(true);
        $this->expectOutputString('FIRED');

        $this->swapConfig(array(
            'rocketeer::strategies.primer' => function () {
                echo 'FIRED';
            },
        ));

        $this->executeCommand('deploy', ['--pretend' => true]);
    }
}
