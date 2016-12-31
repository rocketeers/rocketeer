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

namespace Rocketeer\TestCases\Modules;

use Illuminate\Support\Arr;
use Rocketeer\TestCases\AbstractTask;
use Rocketeer\TestCases\Assertion;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 */
trait Assertions
{
    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// FILESYSTEM /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $filename
     */
    public function assertVirtualFileNotExists($filename)
    {
        $this->assertFalse($this->files->has($filename), 'Failed asserting that file '.$filename.' does not exist');
    }

    /**
     * @param string $filename
     */
    public function assertVirtualFileExists($filename)
    {
        $this->assertTrue($this->files->has($filename), 'Failed asserting that file '.$filename.' exists');
    }

    /**
     * Assert that the number of files in local matches expected.
     *
     * @param array $files
     */
    public function assertListDirectory($files)
    {
        sort($files);

        $this->assertEquals(static::$currentFiles, array_values($files));
    }

    /**
     * Assert that an even will be fired.
     *
     * @param string $event
     */
    protected function expectFiredEvent($event)
    {
        $this->expectOutputRegex('/'.$event.'/');

        $this->tasks->listenTo($event, function () use ($event) {
            echo $event;
        });
    }

    /**
     * Assert that the current connection is a specific one.
     *
     * @param string $connection
     */
    protected function assertConnectionEquals($connection)
    {
        $this->assertEquals($connection, $this->connections->getCurrentConnectionKey()->name);
    }

    /**
     * Assert that the current server is a specific one.
     *
     * @param int $server
     */
    protected function assertCurrentServerEquals($server)
    {
        $this->assertEquals($server, $this->connections->getCurrentConnectionKey()->server);
    }

    /**
     * Assert that the current repository equals.
     *
     * @param string $repository
     */
    protected function assertRepositoryEquals($repository)
    {
        $this->assertEquals($repository, $this->credentials->getCurrentRepository()->repository);
    }

    /**
     * Assert an option has a certain value.
     *
     * @param string $value
     * @param string $option
     */
    protected function assertOptionValueEquals($value, $option)
    {
        $this->assertEquals($value, $this->config->getContextually($option));
    }

    /**
     * Assert a task has a particular output.
     *
     * @param string   $task
     * @param string   $output
     * @param \Mockery $options
     *
     * @return Assertion
     */
    protected function assertTaskOutput($task, $output, $options = null)
    {
        $task = $this->pretendTask($task, $options);

        return $this->assertContains($output, $task->execute());
    }

    /**
     * Assert a task's history matches an array.
     *
     * @param string|AbstractTask $task
     * @param array               $expectedHistory
     * @param array               $options
     *
     * @return string
     */
    protected function assertTaskHistory($task, array $expectedHistory, array $options = [])
    {
        // Create task if needed
        if (is_string($task)) {
            $task = $this->pretendTask($task, $options);
        }

        // Execute task and get history
        if (is_array($task)) {
            $results = '';
            $taskHistory = $task;
        } else {
            $results = $task->execute();
            $taskHistory = $task->history->getFlattenedHistory();
        }

        $this->assertHistory($expectedHistory, $taskHistory);

        return $results;
    }

    /**
     * Assert an history matches another.
     *
     * @param array $expected
     * @param array $obtained
     */
    public function assertHistory(array $expected, array $obtained = [])
    {
        if (!$obtained) {
            $obtained = $this->history->getFlattenedHistory();
        }

        $expected = $this->transformHistory($expected, $obtained);
        $obtained = array_values($obtained);

        // Check equality
        $this->assertEquals($expected, $obtained);
    }

    /**
     * Assert the history contains a particular entry.
     *
     * @param string|string[] $expected
     */
    public function assertHistoryContains($expected)
    {
        $obtained = $this->history->getFlattenedHistory();
        $expected = $this->transformHistory($expected, $obtained);
        $expected = count($expected) === 1 ? $expected[0] : $expected;

        $this->assertContains($expected, $obtained);
    }

    /**
     * Assert the history does not contains a particular entry.
     *
     * @param array $expected
     */
    public function assertHistoryNotContains($expected)
    {
        $obtained = $this->history->getFlattenedHistory();
        $expected = $this->transformHistory($expected, $obtained);
        $expected = count($expected) === 1 ? $expected[0] : $expected;

        $this->assertNotContains($expected, $obtained);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Replace placeholders in an history.
     *
     * @param array       $history
     * @param int|null    $release
     * @param string|null $time
     *
     * @return array
     */
    protected function replaceHistoryPlaceholders($history, $release = null, $time = null)
    {
        $release = $release ?: date('YmdHis');
        $time = $time ?: time();

        $replaced = [];
        foreach ($history as $key => $entries) {
            if (is_array($entries)) {
                $replaced[$key] = $this->replaceHistoryPlaceholders($entries, $release);
                continue;
            }

            $replaced[$key] = strtr($entries, [
                '{php}' => static::$binaries['php'],
                '{rsync}' => static::$binaries['rsync'],
                '{bundle}' => static::$binaries['bundle'],
                '{phpunit}' => static::$binaries['phpunit'],
                '{repository}' => 'https://github.com/'.$this->repository,
                '{server}' => $this->server,
                '{storage}' => $this->paths->getStoragePath(),
                '{release}' => $release,
                '{composer}' => static::$binaries['composer'],
                '{time}' => $time,
            ]);
        }

        return $replaced;
    }

    /**
     * @param string|array $expected
     * @param array        $obtained
     *
     * @return array
     */
    protected function transformHistory($expected, array $obtained)
    {
        // Look for release in history
        $flattened = implode(Arr::flatten($obtained));
        preg_match_all('/[0-9]{14}/', $flattened, $releases);
        $release = Arr::get($releases, '0.0', date('YmdHis'));
        $nextRelease = Arr::get($releases, '0.1');
        if (substr($release, -5) === '00000' && $nextRelease && substr($nextRelease, -5) !== "0000") {
            $release = $nextRelease ?: date('YmdHis');
        }

        // Look for times
        preg_match_all('/tmp\/(\d{10})/', $flattened, $time);
        $time = Arr::get($time, '1.0');

        // Replace placeholders
        $expected = (array) $expected;
        $expected = $this->replaceHistoryPlaceholders($expected, $release, $time);

        return array_values($expected);
    }
}
