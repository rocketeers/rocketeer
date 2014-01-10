<?php
namespace Rocketeer\Tests\Tasks;

use Rocketeer\Tests\TestCases\RocketeerTestCase;

class UpdateTest extends RocketeerTestCase
{
	public function testCanUpdateRepository()
	{
		$task = $this->pretendTask('Update', array(
			'migrate' => true,
			'seed'    => true
		));

		$php     = exec('which php');
		$matcher = array(
		  array(
		    "cd " .$this->server. "/releases/20000000000000",
		    "git reset --hard",
		    "git pull"
		  ),
		  "mkdir -p " .$this->server. "/shared/tests",
		  "mv " .$this->server. "/releases/20000000000000/tests/Elements " .$this->server. "/shared/tests/Elements",
		  array(
		    "cd " .$this->server. "/releases/20000000000000",
		    "chmod -R 755 " .$this->server. "/releases/20000000000000/tests",
		    "chmod -R g+s " .$this->server. "/releases/20000000000000/tests",
		    "chown -R www-data:www-data " .$this->server. "/releases/20000000000000/tests"
		  ),
		  array(
		    "cd " .$this->server. "/releases/20000000000000",
		    "/usr/local/bin/php artisan migrate --seed"
		  ),
		  array(
		    "cd " .$this->server. "/releases/20000000000000",
		    "/usr/local/bin/php artisan cache:clear"
		  )
		);

		$this->assertTaskHistory($task, $matcher);
	}
}
