<?php
namespace Rocketeer\Services;

use Rocketeer\TestCases\RocketeerTestCase;

class CredentialsGathererTest extends RocketeerTestCase
{
	protected $key = '/.ssh/id_rsa';

	public function setUp()
	{
		parent::setUp();

		$this->repository = 'git@github.com:Anahkiasen/rocketeer.git';
		$this->username   = 'Anahkiasen';
		$this->password   = 'foobar';
		$this->host       = 'some.host';
	}

	public function testIgnoresPlaceholdersWhenFillingCredentials()
	{
		$this->mockAnswers(array(
			'No repository is set for [repository]' => $this->repository,
			'No username is set for [repository]'   => $this->username,
			'No password is set for [repository]'   => $this->password,
		));
		$this->command->shouldReceive('option')->andReturn(null);

		$this->givenConfiguredRepositoryCredentials(['repository' => '{foobar}']);

		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
			'username'   => $this->username,
			'password'   => $this->password,
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
		$this->command->shouldReceive('option')->andReturn(null);

		$this->givenConfiguredRepositoryCredentials([]);

		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
			'username'   => $this->username,
			'password'   => $this->password,
		));

		$this->credentials->getRepositoryCredentials();
	}

	public function testDoesntAskForRepositoryCredentialsIfUneeded()
	{
		$this->mockAnswers();
		$this->command->shouldReceive('option')->andReturn(null);

		$this->givenConfiguredRepositoryCredentials([
			'repository' => $this->repository,
			'username'   => null,
			'password'   => null,
		], false);
		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
			'username'   => null,
			'password'   => null,
		));

		$this->credentials->getRepositoryCredentials();
	}

	public function testCanFillRepositoryCredentialsIfNeeded()
	{
		$this->mockAnswers(array(
			'No username is set for [repository]' => $this->username,
			'No password is set for [repository]' => null,
		));
		$this->command->shouldReceive('option')->andReturn(null);

		$this->givenConfiguredRepositoryCredentials(['repository' => $this->repository], true);

		$this->assertStoredCredentialsEquals(array(
			'repository' => $this->repository,
			'username'   => 'Anahkiasen',
			'password'   => null,
		));

		$this->credentials->getRepositoryCredentials();
	}

	public function testCanGetServerCredentialsIfNoneDefined()
	{
		$this->swapConfig(array(
			'remote.connections' => [],
		));

		$this->mockAnswers(array(
			'No host is set for [production]'     => $this->host,
			'No username is set for [production]' => $this->username,
			'No password is set for [production]' => $this->password,
		));

		$this->command->shouldReceive('askWith')->with('No connections have been set, please create one:', 'production')->andReturn('production');
		$this->command->shouldReceive('askWith')->with(
			'No password or SSH key is set for [production], which would you use?',
			'key', ['key', 'password']
		)->andReturn('password');
		$this->command->shouldReceive('option')->andReturn(null);

		$this->credentials->getServerCredentials();

		$credentials = $this->connections->getServerCredentials('production', 0);
		$this->assertEquals(array(
			'host'      => $this->host,
			'username'  => $this->username,
			'password'  => $this->password,
			'keyphrase' => null,
			'key'       => null,
			'agent'     => null,
		), $credentials);
	}

	public function testCanPassCredentialsAsFlags()
	{
		$this->swapConfig(array(
			'remote.connections' => [],
		));

		$this->mockAnswers(array(
			'No username is set for [production]' => $this->username,
		));

		$this->command->shouldReceive('askWith')->with('No connections have been set, please create one:', 'production')->andReturn('production');
		$this->command->shouldReceive('askWith')->with(
			'No password or SSH key is set for [production], which would you use?',
			'key', ['key', 'password']
		)->andReturn('password');
		$this->command->shouldReceive('option')->with('host')->andReturn($this->host);
		$this->command->shouldReceive('option')->with('password')->andReturn($this->password);
		$this->command->shouldReceive('option')->andReturn(null);

		$this->credentials->getServerCredentials();

		$credentials = $this->connections->getServerCredentials('production', 0);
		$this->assertEquals(array(
			'host'      => $this->host,
			'username'  => $this->username,
			'password'  => $this->password,
			'keyphrase' => null,
			'key'       => null,
			'agent'     => null,
		), $credentials);
	}

	public function testCanGetCredentialsForSpecifiedConnection()
	{
		$key = $this->paths->getDefaultKeyPath();
		$this->mockAnswers(array(
			'No host is set for [staging/0]'         => $this->host,
			'No username is set for [staging/0]'     => $this->username,
			'If a keyphrase is required, provide it' => 'KEYPHRASE',
		));

		$this->command->shouldReceive('option')->with('on')->andReturn('staging');
		$this->command->shouldReceive('option')->andReturn(null);
		$this->command->shouldReceive('askWith')->with(
			'Please enter the full path to your key', $key
		)->andReturn($key);
		$this->command->shouldReceive('askWith')->with(
			'No password or SSH key is set for [staging/0], which would you use?',
			'key', ['key', 'password']
		)->andReturn('key');

		$this->credentials->getServerCredentials();

		$credentials = $this->connections->getServerCredentials('staging', 0);
		$this->assertEquals(array(
			'host'      => $this->host,
			'username'  => $this->username,
			'password'  => null,
			'keyphrase' => 'KEYPHRASE',
			'key'       => $key,
			'agent'     => null,
		), $credentials);
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
				$question = strpos($question, 'is set for') !== false ? $question.', please provide one:' : $question;
				$method   = strpos($question, 'password') !== false ? 'askSecretly' : 'askWith';
				$mock     = $mock->shouldReceive($method)->with($question)->andReturn($answer);
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
	protected function givenConfiguredRepositoryCredentials(array $credentials, $need = false)
	{
		$this->mock('rocketeer.connections', 'ConnectionsHandler', function ($mock) use ($need, $credentials) {
			return $mock
				->shouldReceive('needsCredentials')->andReturn($need)
				->shouldReceive('getRepositoryCredentials')->andReturn($credentials);
		});
	}
}
