<?php
namespace Services;

use Mockery;
use Rocketeer\TestCases\RocketeerTestCase;

class CredentialsGathererTest extends RocketeerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->repository = 'git@github.com:Anahkiasen/rocketeer.git';
		$this->username   = 'Anahkiasen';
		$this->password   = 'foobar';
	}

	public function testIgnoresPlaceholdersWhenFillingCredentials()
	{
		$this->mockAnswers(array(
			'No repository is set for [repository]' => $this->repository,
			'No username is set for [repository]'   => $this->username,
			'No password is set for [repository]'   => $this->password,
		));

		$this->givenConfiguredCredentials(['repository' => '{foobar}']);

		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
			'username'   => $this->username,
			'password'   => $this->password
		));

		$this->credentials->getRepositoryCredentials();
	}

	public function testCanGetRepositoryCredentials()
	{
		$this->mockAnswers(array(
			'No repository is set for [repository]' => $this->repository,
			'No username is set for [repository]'   => $this->username,
			'No password is set for [repository]'   => $this->password,
		));

		$this->givenConfiguredCredentials([]);

		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
			'username'   => $this->username,
			'password'   => $this->password
		));

		$this->credentials->getRepositoryCredentials();
	}

	public function testDoesntAskForRepositoryCredentialsIfUneeded()
	{
		$this->mockAnswers();
		$this->givenConfiguredCredentials(['repository' => $this->repository], false);
		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
		));

		$this->credentials->getRepositoryCredentials();
	}

	public function testCanFillRepositoryCredentialsIfNeeded()
	{
		$this->mockAnswers(array(
			'No username is set for [repository]' => $this->username,
		));

		$this->givenConfiguredCredentials(['repository' => $this->repository], true);

		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
			'username'   => 'Anahkiasen',
			'password'   => null,
		));

		$this->credentials->getRepositoryCredentials();
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Mock a set of question/answers
	 *
	 * @param array $answers
	 */
	protected function mockAnswers($answers = array())
	{
		$this->mock('rocketeer.command', 'Command', function ($mock) use ($answers) {
			if (!$answers) {
				return $mock->shouldReceive('ask')->never();
			}

			foreach ($answers as $question => $answer) {
				$mock = $mock->shouldReceive('askWith')->with($question.', please provide one:')->andReturn($answer);
			}

			return $mock;
		});
	}

	/**
	 * Assert a certain set of credentials are saved to storage
	 *
	 * @param array $credentials
	 */
	protected function assertStoredCredentialsEquals(array $credentials)
	{
		$this->mock('rocketeer.storage.local', 'LocalStorage', function ($mock) use ($credentials) {
			return $mock->shouldReceive('set')->with('credentials', $credentials);
		});
	}

	/**
	 * @param array   $credentials
	 * @param boolean $need
	 */
	protected function givenConfiguredCredentials(array $credentials, $need = false)
	{
		$this->mock('rocketeer.connections', 'ConnectionsHandler', function ($mock) use ($need, $credentials) {
			return $mock
				->shouldReceive('needsCredentials')->andReturn($need)
				->shouldReceive('getRepositoryCredentials')->andReturn($credentials);
		});
	}
}
