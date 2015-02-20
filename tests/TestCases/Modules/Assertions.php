<?php
namespace Rocketeer\TestCases\Modules;

use Illuminate\Support\Arr;
use Rocketeer\TestCases\AbstractTask;
use Rocketeer\TestCases\Assertion;

/**
 * @mixin \Rocketeer\TestCases\RocketeerTestCase
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
trait Assertions
{
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
        $this->assertEquals($connection, $this->connections->getCurrentConnection()->name);
    }

    /**
     * Assert that the current server is a specific one.
     *
     * @param integer $server
     */
    protected function assertCurrentServerEquals($server)
    {
        $this->assertEquals($server, $this->connections->getCurrentConnection()->server);
    }

    /**
     * Assert that the current repository equals.
     *
     * @param string $repository
     */
    protected function assertRepositoryEquals($repository)
    {
        $this->assertEquals($repository, $this->credentials->getCurrentRepository()->endpoint);
    }

    /**
     * Assert an option has a certain value.
     *
     * @param string $value
     * @param string $option
     */
    protected function assertOptionValueEquals($value, $option)
    {
        $this->assertEquals($value, $this->rocketeer->getOption($option));
    }

    /**
     * Assert a task has a particular output.
     *
     * @param string   $task
     * @param string   $output
     * @param \Mockery $command
     *
     * @return Assertion
     */
    protected function assertTaskOutput($task, $output, $command = null)
    {
        if ($command) {
            $this->app['rocketeer.command'] = $command;
        }

        return $this->assertContains($output, $this->task($task)->execute());
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
    protected function assertTaskHistory($task, array $expectedHistory, array $options = array())
    {
        // Create task if needed
        if (is_string($task)) {
            $task = $this->pretendTask($task, $options);
        }

        // Execute task and get history
        if (is_array($task)) {
            $results     = '';
            $taskHistory = $task;
        } else {
            $results     = $task->execute();
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
     * @param array $expected
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
     * @param array        $history
     * @param integer|null $release
     *
     * @return array
     */
    protected function replaceHistoryPlaceholders($history, $release = null)
    {
        $release = $release ?: date('YmdHis');
        $hhvm    = defined('HHVM_VERSION');

        $replaced = [];
        foreach ($history as $key => $entries) {
            if ($hhvm && $entries === '{php} -m') {
                continue;
            }

            if (is_array($entries)) {
                $replaced[$key] = $this->replaceHistoryPlaceholders($entries, $release);
                continue;
            }

            $replaced[$key] = strtr($entries, array(
                '{php}'        => $this->binaries['php'],
                '{bundle}'     => $this->binaries['bundle'],
                '{phpunit}'    => $this->binaries['phpunit'],
                '{repository}' => 'https://github.com/'.$this->repository,
                '{server}'     => $this->server,
                '{release}'    => $release,
                '{composer}'   => $this->binaries['composer'],
            ));
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
        $release = implode(array_flatten($obtained));
        preg_match_all('/[0-9]{14}/', $release, $releases);
        $release = Arr::get($releases, '0.0', date('YmdHis'));
        if ($release === '10000000000000') {
            $release = Arr::get($releases, '0.1', date('YmdHis'));
        }

        // Replace placeholders
        $expected = (array) $expected;
        $expected = $this->replaceHistoryPlaceholders($expected, $release);

        return array_values($expected);
    }
}
