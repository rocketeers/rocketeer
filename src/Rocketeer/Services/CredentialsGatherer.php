<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Services;

use Rocketeer\Traits\HasLocator;

class CredentialsGatherer
{
	use HasLocator;

	/**
	 * Get the Repository's credentials
	 */
	public function getRepositoryCredentials()
	{
		// Check for repository credentials
		$repositoryCredentials = $this->connections->getRepositoryCredentials();

		// Build credentials array
		// null values are considered non required
		$credentials = array(
			'repository' => true,
			'username'   => !is_null(array_get($repositoryCredentials, 'username', '')),
			'password'   => !is_null(array_get($repositoryCredentials, 'password', '')),
		);

		// If we didn't specify a login/password ask for both the first time
		if ($this->connections->needsCredentials()) {
			// Else assume the repository is passwordless and only ask again for username
			$credentials += ['username' => true, 'password' => true];
		}

		// Gather credentials
		$credentials = $this->gatherCredentials($credentials, $repositoryCredentials, 'repository');

		// Save them to local storage and runtime configuration
		$this->localStorage->set('credentials', $credentials);
		foreach ($credentials as $key => $credential) {
			$this->config->set('rocketeer::scm.'.$key, $credential);
		}
	}

	/**
	 * Get the LocalStorage's credentials
	 */
	public function getServerCredentials()
	{
		if ($connections = $this->command->option('on')) {
			$this->connections->setConnections($connections);
		}

		// Check for configured connections
		$availableConnections = $this->connections->getAvailableConnections();
		$activeConnections    = $this->connections->getConnections();

		// If we didn't set any connection, ask for them
		if (!$activeConnections or empty($availableConnections)) {
			$connectionName = $this->command->askWith('No connections have been set, please create one:', 'production');
			$this->getConnectionCredentials($connectionName);

			return;
		}

		// Else loop through the connections and fill in credentials
		foreach ($activeConnections as $connectionName) {
			$servers = array_get($availableConnections, $connectionName.'.servers');
			$servers = array_keys($servers);
			foreach ($servers as $server) {
				$this->getConnectionCredentials($connectionName, $server);
			}
		}
	}

	/**
	 * Verifies and stores credentials for the given connection name
	 *
	 * @param string       $connectionName
	 * @param integer|null $server
	 */
	protected function getConnectionCredentials($connectionName, $server = null)
	{
		// Get the available connections
		$connections = $this->connections->getAvailableConnections();

		// Get the credentials for the asked connection
		$connection = $connectionName.'.servers';
		$connection = !is_null($server) ? $connection.'.'.$server : $connection;
		$connection = array_get($connections, $connection, []);

		// Update connection name
		$handle = !is_null($server) ? $connectionName.'#'.$server : $connectionName;

		// Gather credentials
		$credentials = $this->gatherCredentials(array(
			'host'      => true,
			'username'  => true,
			'password'  => false,
			'keyphrase' => null,
			'key'       => false,
			'agent'     => false
		), $connection, $handle);

		// Get password or key
		if (!$credentials['password'] and !$credentials['key']) {
			$types = ['key', 'password'];
			$type  = $this->command->askWith('No password or SSH key is set for ['.$handle.'], which would you use?', 'key', $types);
			if ($type == 'key') {
				$default                  = $this->rocketeer->getDefaultKeyPath();
				$credentials['key']       = $this->command->option('key') ?: $this->command->askWith('Please enter the full path to your key', $default);
				$credentials['keyphrase'] = $this->gatherCredential($handle, 'keyphrase', 'If a keyphrase is required, provide it');
			} else {
				$credentials['password'] = $this->gatherCredential($handle, 'password');
			}
		}

		// Save credentials
		$this->connections->syncConnectionCredentials($connectionName, $credentials, $server);
		$this->connections->setConnection($connectionName);
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Loop through credentials and store the missing ones
	 *
	 * @param boolean[] $credentials
	 * @param string[]  $current
	 * @param string    $handle
	 *
	 * @return array
	 */
	protected function gatherCredentials($credentials, $current, $handle)
	{
		// Loop throguh credentials and ask missing ones
		foreach ($credentials as $credential => $required) {
			$$credential = $this->getCredential($current, $credential);
			if ($required and !$$credential) {
				$$credential = $this->gatherCredential($handle, $credential);
			}
		}

		// Reform array
		$credentials = compact(array_keys($credentials));

		return $credentials;
	}

	/**
	 * Look for a credential in the flags or ask for it
	 *
	 * @param string      $handle
	 * @param string      $credential
	 * @param string|null $question
	 *
	 * @return string
	 */
	protected function gatherCredential($handle, $credential, $question = null)
	{
		$question = $question ?: 'No '.$credential.' is set for ['.$handle.'], please provide one:';
		$option   = $this->command->option($credential);

		return $option ?: $this->command->askWith($question);
	}

	/**
	 * Check if a credential needs to be filled
	 *
	 * @param string[] $credentials
	 * @param string   $credential
	 *
	 * @return string
	 */
	protected function getCredential($credentials, $credential)
	{
		$value = array_get($credentials, $credential);
		if (substr($value, 0, 1) === '{') {
			return;
		}

		return $value ?: $this->command->option($credential);
	}
}
