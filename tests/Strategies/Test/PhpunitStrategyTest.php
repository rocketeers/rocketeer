<?php
namespace Rocketeer\Strategies\Test;

use Rocketeer\TestCases\RocketeerTestCase;

class PhpunitStrategyTest extends RocketeerTestCase
{
    public function testCanRunTests()
    {
        $this->pretendTask();
        $this->builder->buildStrategy('Test', 'Phpunit')->test();

        $this->assertHistory(array(
            array(
                'cd {server}/releases/20000000000000',
                '{phpunit} --stop-on-failure',
            ),
        ));
    }

    public function testCanRunTestsInSubfolder()
    {
        $this->swapConfig(['remote.subdirectory' => 'laravel']);

        $this->pretendTask();
        $this->builder->buildStrategy('Test', 'Phpunit')->test();

        $this->assertHistory(array(
            array(
                'cd {server}/releases/20000000000000/laravel',
                '{phpunit} --stop-on-failure',
            ),
        ));
    }
}
