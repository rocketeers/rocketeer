<?php
namespace Rocketeer\Tasks;

use Rocketeer\TestCases\RocketeerTestCase;

class UpdateTest extends RocketeerTestCase
{
    public function testCanUpdateRepository()
    {
        $task = $this->pretendTask('Update', array(
            'migrate' => true,
            'seed'    => true,
        ));

        $matcher = array(
            array(
                "cd {server}/releases/20000000000000",
                "git reset --hard",
                "git pull",
            ),
            array(
                "cd {server}/releases/20000000000000",
                "chmod -R 755 {server}/releases/20000000000000/tests",
                "chmod -R g+s {server}/releases/20000000000000/tests",
                "chown -R www-data:www-data {server}/releases/20000000000000/tests",
            ),
            array(
                "cd {server}/releases/20000000000000",
                "{php} artisan cache:clear",
            ),
        );

        $this->assertTaskHistory($task, $matcher);
    }

    public function testCanDisableCacheClearing()
    {
        $task = $this->pretendTask('Update', array(
            'migrate'  => true,
            'seed'     => true,
            'no-clear' => true,
        ));

        $matcher = array(
            array(
                "cd {server}/releases/20000000000000",
                "git reset --hard",
                "git pull",
            ),
            array(
                "cd {server}/releases/20000000000000",
                "chmod -R 755 {server}/releases/20000000000000/tests",
                "chmod -R g+s {server}/releases/20000000000000/tests",
                "chown -R www-data:www-data {server}/releases/20000000000000/tests",
            ),
        );

        $this->assertTaskHistory($task, $matcher);
    }
}
