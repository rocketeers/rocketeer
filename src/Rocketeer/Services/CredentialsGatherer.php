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

use Illuminate\Support\Arr;
use Rocketeer\Traits\HasLocator;

class CredentialsGatherer
{
    use HasLocator;

    /**
     * Get the Repository's credentials.
     */
    public function getRepositoryCredentials()
    {
        // Check for repository credentials
        $repositoryCredentials = $this->connections->getRepositoryCredentials();

        // Build credentials array
        // null values are considered non required
        $credentials = [
            'repository' => true,
            'username'   => !is_null(Arr::get($repositoryCredentials, 'username', '')),
            'password'   => !is_null(Arr::get($repositoryCredentials, 'password', '')),
        ];

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
     * Get the LocalStorage's credentials.
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
        if (!$activeConnections || empty($availableConnections)) {
            $connectionName = $this->command->askWith('No connections have been set, please create one:', 'production');
            $this->getConnectionCredentials($connectionName);

            return;
        }

        // Else loop through the connections and fill in credentials
        foreach ($activeConnections as $connectionName) {
            $servers = Arr::get($availableConnections, $connectionName.'.servers');
            $servers = array_keys($servers);
            foreach ($servers as $server) {
                $this->getConnectionCredentials($connectionName, $server);
            }
        }
    }

    /**
     * Verifies and stores credentials for the given connection name.
     *
     * @param string   $connectionName
     * @param int|null $server
     */
    protected function getConnectionCredentials($connectionName, $server = null)
    {
        // Get the available connections
        $connections = $this->connections->getAvailableConnections();

        // Get the credentials for the asked connection
        $connection = $connectionName.'.servers';
        $connection = !is_null($server) ? $connection.'.'.$server : $connection;
        $connection = Arr::get($connections, $connection, []);

        // Update connection name
        $handle = $this->connections->getHandle($connectionName, $server);

        // Gather common credentials
        $credentials = $this->gatherCredentials([
            'host'      => true,
            'username'  => true,
            'password'  => false,
            'keyphrase' => null,
            'key'       => false,
            'agent'     => false,
        ], $connection, $handle);

        // Get password or key
        $credentials = $this->getConnectionAuthentication($credentials, $handle);

        // Save credentials
        $this->connections->syncConnectionCredentials($connectionName, $credentials, $server);
        $this->connections->setConnection($connectionName);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Smart fill-in of the key/password of a connection.
     *
     * @param string[] $credentials
     * @param string   $handle
     *
     * @return string[]
     */
    protected function getConnectionAuthentication(array $credentials, $handle)
    {
        // Cancel if already provided
        if ($credentials['password'] || $credentials['key']) {
            return $credentials;
        }

        // Get which type of authentication to use
        $types   = ['key', 'password'];
        $keyPath = $this->paths->getDefaultKeyPath();
        $type    = $this->command->askWith('No password or SSH key is set for ['.$handle.'], which would you use?', 'key', $types);

        // Gather the credentials for each
        switch ($type) {
            case 'key':
                $credentials['key']       = $this->command->option('key') ?: $this->command->askWith('Please enter the full path to your key', $keyPath);
                $credentials['keyphrase'] = $this->gatherCredential($handle, 'keyphrase', 'If a keyphrase is required, provide it');
                break;

            case 'password':
                $credentials['password'] = $this->gatherCredential($handle, 'password');
                break;
        }

        return $credentials;
    }

    /**
     * Loop through credentials and store the missing ones.
     *
     * @param boolean[] $credentials
     * @param string[]  $current
     * @param string    $handle
     *
     * @return string[]
     */
    protected function gatherCredentials($credentials, $current, $handle)
    {
        // Loop through credentials and ask missing ones
        foreach ($credentials as $credential => $required) {
            $$credential = $this->getCredential($current, $credential);
            if ($required && !$$credential) {
                $$credential = $this->gatherCredential($handle, $credential);
            }
        }

        // Reform array
        $credentials = compact(array_keys($credentials));

        return $credentials;
    }

    /**
     * Look for a credential in the flags or ask for it.
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
        $method   = in_array($credential, ['password', 'keyphrase'], true) ? 'askSecretly' : 'askWith';

        return $option ?: $this->command->$method($question);
    }

    /**
     * Check if a credential needs to be filled.
     *
     * @param string[] $credentials
     * @param string   $credential
     *
     * @return string
     */
    protected function getCredential($credentials, $credential)
    {
        $value = Arr::get($credentials, $credential);
        if (substr($value, 0, 1) === '{') {
            return;
        }

        return $value ?: $this->command->option($credential);
    }
}
