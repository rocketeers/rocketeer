<?php
namespace Rocketeer\Tasks;

class Check extends Task
{

	/**
	 * Run the Task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$errors = array();

		// Check PHP
		if (!$this->checkPhpVersion()) {
			$errors[] = $this->command->error('The version of PHP on the server does not match Larvel\'s requirements');
		}

		// Check MCrypt
		if (!$this->checkPhpExtension('mcrypt')) {
			$errors[] = $this->command->error('MCrypt is not installed on the remote server');
		}

		// Check Composer
		if (!$this->checkComposer()) {
			$errors[] = $this->command->error('Composer does not seem to be present on the server');
		}

		// Return false if any error
		if (!empty($errors)) {
			return false;
		}

		// Display confirmation message
		$this->command->info('Your server is ready to deploy');

		return true;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Check if Composer is on the server
	 *
	 * @return boolean
	 */
	public function checkComposer()
	{
		return $this->getComposer();
	}

	/**
	 * Check if the server is ready to support PHP
	 *
	 * @return boolean
	 */
	public function checkPhpVersion()
	{
		$version = $this->run('php -r "print PHP_VERSION;"');

		return version_compare($version, '5.3.7', '>=');
	}

	/**
	 * Check the presence of a PHP extension
	 *
	 * @param  string $extension    The extension
	 *
	 * @return boolean
	 */
	public function checkPhpExtension($extension)
	{
		$hasExtension = $this->run('php -m | grep '.$extension);

		return $hasExtension == $extension;
	}

}