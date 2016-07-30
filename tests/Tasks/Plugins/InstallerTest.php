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

namespace Rocketeer\Tasks\Plugins;

use Rocketeer\TestCases\RocketeerTestCase;

class InstallerTest extends RocketeerTestCase
{
    public function testCanInstallPlugin()
    {
        $this->assertTaskHistory(Installer::class, [
            'bash --login -c \'{composer} require anahkiasen/rocketeer-slack --update-no-dev --working-dir="'.$this->paths->getRocketeerPath().'"\'',
        ], [
            'package' => 'anahkiasen/rocketeer-slack',
        ]);
    }
}
