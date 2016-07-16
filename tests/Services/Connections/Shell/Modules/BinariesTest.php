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

namespace Rocketeer\Services\Connections\Shell\Modules;

use Rocketeer\Binaries\Php;
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
        $this->mockRemote([
            'which foobar' => 'foobar not found',
            'which npm' => 'which: no npm in (/usr/local/bin:/bin:/usr/bin)',
        ]);

        $this->assertEquals(false, $this->bash->rawWhich('foobar'));
        $this->assertEquals(false, $this->bash->rawWhich('npm'));
    }

    public function testStoredPathsAreInvalidatedIfIncorrect()
    {
        $this->mockRemote([
            'which foobar' => null,
            'which composer' => 'composer',
        ]);

        $this->localStorage->set('paths.production.composer', 'foobar');

        $this->assertEquals('composer', $this->task->which('composer'));
        $this->assertEquals('composer', $this->localStorage->get('paths.production.composer'));
    }

    public function testPathsAreScopedToConnection()
    {
        $this->mockRemote([
            'which production' => 'production',
            'which staging' => 'staging',
        ]);

        $this->localStorage->set('paths.production.composer', 'production');
        $this->localStorage->set('paths.staging.composer', 'staging');

        $this->assertEquals('production', $this->task->which('composer'));
        $this->connections->setCurrentConnection('staging');
        $this->assertEquals('staging', $this->task->which('composer'));
    }

    public function testCanSetPathToPhpAndArtisan()
    {
        $this->usesLaravel(true);

        $this->mockConfig([
            'paths.php' => static::$binaries['php'],
            'paths.artisan' => static::$binaries['php'],
        ]);

        $this->assertEquals(static::$binaries['php'].' '.static::$binaries['php'].' migrate --force',
            $this->task->artisan()->migrate());
    }

    public function testAlwaysRespectsCustomPath()
    {
        $this->mockConfig([
            'paths.php' => 'foo',
            'paths.composer' => 'php /some/composer.phar',
        ]);

        $this->assertEquals('foo', $this->bash->php()->getBinary());
        $this->assertEquals('php /some/composer.phar', $this->bash->composer()->getBinary());
    }

    public function testFetchesBinaryIfNotSpecifiedOrNull()
    {
        $this->usesLaravel(true);

        $this->mockConfig([
            'paths.php' => static::$binaries['php'],
        ]);

        $this->assertEquals(static::$binaries['php'].' artisan migrate --force', $this->task->artisan()->migrate());
    }

    public function testCanGetBinary()
    {
        $whichGrep = exec('which grep');
        $grep = $this->task->which('grep');

        $this->assertEquals($whichGrep, $grep);
    }

    public function testCanRunComposer()
    {
        $this->usesComposer();

        $this->pretendTask('Dependencies')->execute();

        $this->assertContains('composer install', $this->history->getFlattenedHistory()[0][1]);
    }

    public function testDoesntRunComposerIfNotNeeded()
    {
        $this->usesComposer(false);

        $this->pretendTask('Dependencies')->execute();
        $this->assertEmpty($this->history->getFlattenedHistory());
    }

    public function testCanGetBinariesViaMagicMethods()
    {
        $binary = $this->bash->php();
        $this->assertInstanceOf(Php::class, $binary);
    }

    public function testCanRunBinariesMethodsViaMagicMethods()
    {
        $this->mockRemote([
            'which composer' => 'composer',
            'composer --help' => 'Usage: foobar',
        ]);

        $results = $this->bash->composer('--help');

        $this->assertContains('Usage:', $results);
    }
}
