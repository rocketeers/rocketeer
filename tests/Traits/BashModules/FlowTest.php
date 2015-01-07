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
        $this->config->set('rocketeer::stages.stages', ['foobar']);
        $this->assertTrue($this->task('Deploy')->usesStages());

        $this->config->set('rocketeer::stages.stages', []);
        $this->assertFalse($this->task('Deploy')->usesStages());
    }
}
