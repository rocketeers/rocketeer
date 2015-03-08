<?php
namespace Rocketeer\Traits\BashModules;

use Rocketeer\TestCases\RocketeerTestCase;

class FlowTest extends RocketeerTestCase
{
    public function testCanCopyFilesFromPreviousRelease()
    {
        $this->pretend();
        $this->bash->copyFromPreviousRelease('foobar');

        $this->assertHistory(array(
            'cp -a {server}/releases/10000000000000/foobar {server}/releases/20000000000000/foobar',
        ));
    }

    public function testCanCheckIfUsesStages()
    {
        $this->config->set('stages.stages', ['foobar']);
        $this->assertTrue($this->task('Deploy')->usesStages());

        $this->config->set('stages.stages', []);
        $this->assertFalse($this->task('Deploy')->usesStages());
    }

    public function testCanRunCommandsInSubdirectoryIfRequired()
    {
        $this->pretend();

        $this->swapConfig(['remote.subdirectory' => 'laravel']);
        $this->bash->runForApplication('ls');
        $this->assertHistoryContains(array(
            array(
                'cd {server}/releases/{release}/laravel',
                'ls',
            ),
        ));

        $this->swapConfig(['remote.subdirectory' => null]);
        $this->bash->runForApplication('ls');
        $this->assertHistoryContains(array(
            array(
                'cd {server}/releases/{release}',
                'ls',
            ),
        ));
    }
}
