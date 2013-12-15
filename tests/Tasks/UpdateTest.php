<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\RocketeerTests;

class UpdateTest extends RocketeerTests
{
	public function testCanUpdateRepository()
	{
		$task = $this->pretendTask('Update', array(
			'migrate' => true,
			'seed' => true
		));

		$update = $task->execute();
		$php    = exec('which php');

		$matcher = array(
			array(
				"cd " .$this->server. "/releases/20000000000000",
				"git reset --hard",
				"git pull",
			),
			array(
				"cd " .$this->server. "/releases/20000000000000",
				"chmod -R 755 " .$this->server. "/releases/20000000000000/tests",
				"chmod -R g+s " .$this->server. "/releases/20000000000000/tests",
				"chown -R www-data:www-data " .$this->server. "/releases/20000000000000/tests",
			),
			array(
				"cd " .$this->server. "/releases/20000000000000",
				$php." artisan migrate --seed",
			),
			array(
				"cd " .$this->server. "/releases/20000000000000",
				$php." artisan cache:clear",
			),
		);

		$this->assertEquals($matcher, $update);
	}
}
