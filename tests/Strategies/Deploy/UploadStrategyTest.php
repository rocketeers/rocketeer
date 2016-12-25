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

namespace Rocketeer\Strategies\Deploy;

use Rocketeer\TestCases\RocketeerTestCase;

class UploadStrategyTest extends RocketeerTestCase
{
    public function testDoesntTryToUploadToInexistantFolders()
    {
        $this->swapConfigWithEvents(['strategies.deploy' => 'Upload']);
        $this->pretendTask('Deploy')->fire();
    }
}
