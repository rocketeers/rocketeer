<?php
namespace Rocketeer;

use Rocketeer\TestCases\RocketeerTestCase;

class RocketeerTest extends RocketeerTestCase
{
    public function testCanGetApplicationName()
    {
        $this->assertEquals('foobar', $this->rocketeer->getApplicationName());
    }

    public function testCanUseRecursiveStageConfiguration()
    {
        $this->swapConfig(array(
            'scm.branch'                   => 'master',
            'on.stages.staging.scm.branch' => 'staging',
        ));

        $this->assertOptionValueEquals('master', 'scm.branch');
        $this->connections->setStage('staging');
        $this->assertOptionValueEquals('staging', 'scm.branch');
    }

    public function testCanUseRecursiveConnectionConfiguration()
    {
        $this->swapConfig(array(
            'default'                           => 'production',
            'scm.branch'                        => 'master',
            'on.connections.staging.scm.branch' => 'staging',
        ));
        $this->assertOptionValueEquals('master', 'scm.branch');

        $this->swapConfig(array(
            'default'                           => 'staging',
            'scm.branch'                        => 'master',
            'on.connections.staging.scm.branch' => 'staging',
        ));
        $this->assertOptionValueEquals('staging', 'scm.branch');
    }

    public function testRocketeerCanGuessWhichStageHesIn()
    {
        $path  = '/home/www/foobar/production/releases/12345678901234/app';
        $stage = Rocketeer::getDetectedStage('foobar', $path);
        $this->assertEquals('production', $stage);

        $path  = '/home/www/foobar/staging/releases/12345678901234/app';
        $stage = Rocketeer::getDetectedStage('foobar', $path);
        $this->assertEquals('staging', $stage);

        $path  = '/home/www/foobar/releases/12345678901234/app';
        $stage = Rocketeer::getDetectedStage('foobar', $path);
        $this->assertEquals(false, $stage);
    }
}
