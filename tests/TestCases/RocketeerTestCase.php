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

namespace Rocketeer\TestCases;

abstract class RocketeerTestCase extends ContainerTestCase
{
    /**
     * The test repository.
     *
     * @var string
     */
    protected $repository = 'Anahkiasen/html-object.git';

    /**
     * @var string
     */
    protected $username = 'anahkiasen';

    /**
     * @var string
     */
    protected $password = 'foobar';

    /**
     * A dummy AbstractTask to use for helpers tests.
     *
     * @var \Rocketeer\Tasks\AbstractTask
     */
    protected $task;

    /**
     * Number of files an ls should yield.
     *
     * @var int
     */
    protected static $currentFiles;

    /**
     * Set up the tests.
     */
    public function setUp()
    {
        parent::setUp();

        // Compute ls results
        if (!static::$currentFiles) {
            $files = preg_grep('/^([^.0])/', scandir(__DIR__.'/../..'));
            sort($files);
            static::$currentFiles = array_values($files);
        }

        // Bind dummy AbstractTask
        $this->task = $this->task('Cleanup');

        // Mock current environment
        $this->replicateFolder($this->server);
        $this->files->put(__DIR__.'/../../bin/intro.txt', 'INTRO');
        $this->mockOperatingSystem('Linux');

        // Mark valid releases
        $this->releasesManager->markReleaseAsValid(10000000000000);
        $this->releasesManager->markReleaseAsValid(20000000000000);
    }
}
