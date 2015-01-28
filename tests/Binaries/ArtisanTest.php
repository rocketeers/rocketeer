<?php
namespace Rocketeer\Binaries;

use Rocketeer\TestCases\RocketeerTestCase;

class ArtisanTest extends RocketeerTestCase
{
    public function testCanRunMigrations()
    {
        $php     = $this->binaries['php'];
        $artisan = new Artisan($this->app);

        $commands = $artisan->migrate();
        $this->assertEquals($php.' artisan migrate --force', $commands);
    }

    public function testCanSeedDatabase()
    {
        $php     = $this->binaries['php'];
        $artisan = new Artisan($this->app);

        $commands = $artisan->seed();
        $this->assertEquals($php.' artisan db:seed --force', $commands);
    }
}
