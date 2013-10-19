<?php

class UpdateTest extends RocketeerTests
{
	public function testCanUpdateRepository()
	{
		$task = $this->pretendTask('Update', array(
			'migrate' => true,
			'seed' => true
		));

		$update = $task->execute();
		$composer = exec('which composer');

		$matcher = array(
			array(
				"cd " .$this->server. "/releases/20000000000000",
				"git reset --hard",
				"git pull",
			),
			$composer,
			array(
				"cd " .$this->server. "/releases/20000000000000",
				$composer. " install",
			),
			array(
				"cd " .$this->server. "/releases/20000000000000",
				"chmod -R 755 " .$this->server. "/releases/20000000000000/tests",
				"chmod -R g+s " .$this->server. "/releases/20000000000000/tests",
				"chown -R www-data:www-data " .$this->server. "/releases/20000000000000/tests",
			),
			array(
				"cd " .$this->server. "/releases/20000000000000",
				"php artisan migrate --seed",
			),
			array(
				"cd " .$this->server. "/releases/20000000000000",
				"php artisan cache:clear",
			),
		);

		$this->assertEquals($matcher, $update);
	}
}
