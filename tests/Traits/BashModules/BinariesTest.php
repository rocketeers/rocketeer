<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Traits\BashModules;

use Mockery;
use Mockery\MockInterface;
use Rocketeer\TestCases\RocketeerTestCase;

class BinariesTest extends RocketeerTestCase
{
    public function testCanSetCustomPathsForBinaries()
    {
        $binary = __DIR__.'/../../../bin/rocketeer';
        $this->mockConfig(['paths.composer' => $binary]);

        $this->assertEquals($binary, $this->task->which('composer'));
    }

    public function testConsidersAllPossibleWhichOutputs()
    {
        $this->mock('rocketeer.bash', 'Bash', function ($mockery) {
            return $mockery
                ->shouldReceive('runSilently')->with('which foobar')->andReturn('foobar not found')
                ->shouldReceive('runSilently')->with('which npm')->andReturn('which: no npm in (/usr/local/bin:/bin:/usr/bin)');
        });

        $this->assertEquals(false, $this->bash->rawWhich('foobar'));
        $this->assertEquals(false, $this->bash->rawWhich('npm'));
    }

    public function testStoredPathsAreInvalidatedIfIncorrect()
    {
        $this->mock('rocketeer.remote', 'Remote', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('connected')->andReturn(false)
                ->shouldReceive('run')->with(['which composer'], Mockery::any())->andReturn(null)
                ->shouldReceive('run')->with(['which'], Mockery::any())->andReturn(null)
                ->shouldReceive('run')->with(['which foobar'], Mockery::any())->andReturn('foobar not found')
                ->shouldReceive('run')->with(['which '.$this->binaries['composer']], Mockery::any())->andReturn($this->binaries['composer'])
                ->shouldReceive('runRaw')->andReturn('false');
        }, false);

        $this->localStorage->set('paths.production.composer', 'foobar');

        $this->assertEquals('composer', $this->task->which('composer'));
        $this->assertNull($this->localStorage->get('paths.production.composer'));
    }

    public function testPathsAreScopedToConnection()
    {
        $this->mock('rocketeer.remote', 'Remote', function (MockInterface $mock) {
            return $mock
                ->shouldReceive('connected')->andReturn(false)
                ->shouldReceive('run')->with(['which'], Mockery::any())->andReturn(null)
                ->shouldReceive('run')->with(['which composer'], Mockery::any())->andReturn(null)
                ->shouldReceive('run')->with(['which production'], Mockery::any())->andReturnUsing(function ($a, $b) {
                    $b('production');
                })
                ->shouldReceive('run')->with(['which staging'], Mockery::any())->andReturnUsing(function ($a, $b) {
                    $b('staging');
                })
                ->shouldReceive('runRaw')->andReturn('false');
        }, false);

        $this->localStorage->set('paths.production.composer', 'production');
        $this->localStorage->set('paths.staging.composer', 'staging');

        $this->assertEquals('production', $this->task->which('composer'));
        $this->connections->setConnection('staging');
        $this->assertEquals('staging', $this->task->which('composer'));
    }

    public function testCanSetPathToPhpAndArtisan()
    {
        $this->mockConfig([
            'paths.php'     => $this->binaries['php'],
            'paths.artisan' => $this->binaries['php'],
        ]);

        $this->assertEquals($this->binaries['php'].' '.$this->binaries['php'].' migrate --force', $this->task->artisan()->migrate());
    }

    public function testAlwaysRespectsCustomPath()
    {
        $this->mockConfig([
            'paths.php'      => 'foo',
            'paths.composer' => 'php /some/composer.phar',
        ]);

        $this->assertEquals('foo', $this->bash->php()->getBinary());
        $this->assertEquals('php /some/composer.phar', $this->bash->composer()->getBinary());
    }

    public function testFetchesBinaryIfNotSpecifiedOrNull()
    {
        $this->mockConfig([
            'paths.php' => $this->binaries['php'],
        ]);

        $this->assertEquals($this->binaries['php'].' artisan migrate --force', $this->task->artisan()->migrate());
    }

    public function testCanGetBinary()
    {
        $whichGrep = exec('which grep');
        $grep      = $this->task->which('grep');

        $this->assertEquals($whichGrep, $grep);
    }

    public function testCanRunComposer()
    {
        $this->usesComposer();
        $this->mock('rocketeer.command', 'Illuminate\Console\Command', function (MockInterface $mock) {
            return $mock
                ->shouldIgnoreMissing()
                ->shouldReceive('getOutput')->andReturn($this->getCommandOutput())
                ->shouldReceive('line')
                ->shouldReceive('option')->andReturn([]);
        });

        $this->pretendTask('Dependencies')->execute();

        $this->assertCount(2, $this->history->getFlattenedHistory()[0]);
    }

    public function testDoesntRunComposerIfNotNeeded()
    {
        $this->usesComposer(false);
        $this->mock('rocketeer.command', 'Illuminate\Console\Command', function (MockInterface $mock) {
            return $mock
                ->shouldIgnoreMissing()
                ->shouldReceive('getOutput')->andReturn($this->getCommandOutput())
                ->shouldReceive('line')
                ->shouldReceive('option')->andReturn([]);
        });

        $this->pretendTask('Dependencies')->execute();
        $this->assertEmpty($this->history->getFlattenedHistory());
    }

    public function testCanGetBinariesViaMagicMethods()
    {
        $binary = $this->bash->php();
        $this->assertInstanceOf('Rocketeer\Binaries\Php', $binary);
    }

    public function testCanRunBinariesMethodsViaMagicMethods()
    {
        $results = $this->bash->composer('--help');

        $this->assertContains('Usage:', $results);
    }
}
