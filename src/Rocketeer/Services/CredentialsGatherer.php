<?php
namespace Rocketeer\Services;

use Rocketeer\Traits\HasLocator;

class CredentialsGatherer
{
	use HasLocator;

	/**
	 * Get the Repository's credentials
	 *
	 * @return void
	 */
	public function getRepositoryCredentials()
	{
		// Check for repository credentials
		$repositoryInfos = $this->connections->getRepositoryCredentials();
		$credentials     = ['repository'];
		if (!array_get($repositoryInfos, 'repository') or $this->connections->needsCredentials()) {
			$credentials = ['repository', 'username', 'password'];
		}

		// Gather credentials
		foreach ($credentials as $credential) {
			${$credential} = $this->getCredential($repositoryInfos, $credential);
			if (!${$credential}) {
				${$credential} = $this->command->ask('No '.$credential.' is set for the repository, please provide one :');
			}
		}

		// Save them
		$credentials = compact($credentials);
		$this->app['rocketeer.storage.local']->set('credentials', $credentials);
		foreach ($credentials as $key => $credential) {
			$this->config->set('rocketeer::scm.'.$key, $credential);
		}
	}

	/**
	 * Get the LocalStorage's credentials
	 *
	 * @return void
	 */
	public function getServerCredentials()
	{
		if ($connections = $this->command->option('on')) {
			$this->connections->setConnections($connections);
		}

		// Check for configured connections
		$availableConnections = $this->connections->getAvailableConnections();
		$activeConnections    = $this->connections->getConnections();

		if (count($activeConnections) <= 0) {
			$connectionName = $this->command->ask('No connections have been set, please create one : (production)', 'production');
			$this->storeServerCredentials($availableConnections, $connectionName);
		} else {
			foreach ($activeConnections as $connectionName) {
				$servers = array_get($availableConnections, $connectionName.'.servers');
				$servers = array_keys($servers);
				foreach ($servers as $server) {
					$this->storeServerCredentials($availableConnections, $connectionName, $server);
				}
			}
		}
	}

	/**
	 * Verifies and stores credentials for the given connection name
	 *
	 * @param array        $connections
	 * @param string       $connectionName
	 * @param integer|null $server
	 */
	protected function storeServerCredentials($connections, $connectionName, $server = null)
	{
		// Check for server credentials
		$connection  = $connectionName.'.servers';
		$connection  = !is_null($server) ? $connection.'.'.$server : $connection;
		$connection  = array_get($connections, $connection, array());
		$credentials = array(
			'host'      => true,
			'username'  => true,
			'password'  => false,
			'keyphrase' => null,
			'key'       => false,
			'agent'     => false
		);

		// Update connection name
		$handle = !is_null($server) ? $connectionName.'#'.$server : $connectionName;

		// Gather credentials
		foreach ($credentials as $credential => $required) {
			${$credential} = $this->getCredential($connection, $credential);
			if ($required and !${$credential}) {
				${$credential} = $this->command->ask('No '.$credential.' is set for ['.$handle.'], please provide one :');
			}
		}

		// Get password or key
		if (!$password and !$key) {
			$type = $this->command->ask('No password or SSH key is set for ['.$handle.'], which would you use ? [key/password]', 'key');
			if ($type == 'key') {
				$default   = $this->app['rocketeer.rocketeer']->getUserHomeFolder().'/.ssh/id_rsa';
				$key       = $this->command->ask('Please enter the full path to your key ('.$default.')', $default);
				$keyphrase = $this->command->ask('If a keyphrase is required, provide it');
			} else {
				$password = $this->command->ask('Please enter your password');
			}
		}

		// Save credentials
		$credentials = compact(array_keys($credentials));
		$this->connections->syncConnectionCredentials($connectionName, $credentials, $server);
		$this->connections->setConnection($connectionName);
	}

	/**
	 * Check if a credential needs to be filled
	 *
	 * @param array  $credentials
	 * @param string $credential
	 *
	 * @return string
	 */
	protected function getCredential($credentials, $credential)
	{
		$credential = array_get($credentials, $credential);
		if (substr($credential, 0, 1) === '{') {
			return;
		}

		return $credential;
	}
}
