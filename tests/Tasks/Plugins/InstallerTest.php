<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Tasks\Plugins;

use Rocketeer\TestCases\RocketeerTestCase;

class InstallerTest extends RocketeerTestCase
{
    public function testCanInstallPlugin()
    {
        $this->pretend();
        $this->mockCommand([
            'package' => 'anahkiasen/rocketeer-slack',
        ]);

        $this->assertTaskHistory('Plugins\Installer', [
            'bash --login -c \'{composer} require --working-dir="'.$this->paths->getRocketeerConfigFolder().'"\'',
        ]);
    }
}
