<?php
namespace Rocketeer\Console\Commands\Development;

use Exception;
use Phar;
use PharException;
use Rocketeer\Abstracts\AbstractCommand;
use RuntimeException;
use UnexpectedValueException;

/**
 * Self update command for Rocketeer
 * Largely inspired by Composer's
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class SelfUpdateCommand extends AbstractCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'selfupdate';

	/**
	 * @type string
	 */
	protected $description = 'Update Rocketeer to the latest version';

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	public function fire()
	{
		$latest        = 'http://rocketeer.autopergamene.eu/versions/rocketeer.phar';
		$folder        = $this->laravel['rocketeer.paths']->getRocketeerConfigFolder();
		$localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];

		$this->comment('1. Checking permissions');
		$this->checkPermissions($folder, $localFilename);

		$this->comment('2. Downloading latest PHAR');
		$contents     = $this->getRemoteFileContents($latest);
		$tempFilename = $folder.DS.basename($localFilename, '.phar').'-temp.phar';
		$this->laravel['files']->put($tempFilename, $contents);

		$this->comment('3. Updating Rocketeer');
		if ($exception = $this->updateBinary($localFilename, $tempFilename)) {
			$this->error('An error occured when updating Rocketeer: '.$exception->getMessage());

			return 1;
		}
	}

	/**
	 * Get the contents of a remote file
	 *
	 * @param string $latest
	 *
	 * @return string
	 */
	protected function getRemoteFileContents($latest)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $latest);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);

		$contents = curl_exec($curl);
		curl_close($curl);

		return $contents;
	}

	/**
	 * Update the local binary
	 *
	 * @param string $localFilename
	 * @param string $newFilename
	 *
	 * @return Exception
	 * @throws Exception
	 */
	protected function updateBinary($localFilename, $newFilename)
	{
		try {
			@chmod($newFilename, 0777 & ~umask());
			if (!ini_get('phar.readonly')) {
				$phar = new Phar($newFilename);
				unset($phar);
			}
			rename($newFilename, $localFilename);
		} catch (Exception $exception) {
			if (!$exception instanceof UnexpectedValueException && !$exception instanceof PharException) {
				throw $exception;
			}

			return $exception;
		}
	}

	/**
	 * @param string $folder
	 * @param string $localFilename
	 */
	protected function checkPermissions($folder, $localFilename)
	{
		if (!is_writable($folder)) {
			throw new RuntimeException('Updated failed: temporary folder '.$folder.' used for download could not be written');
		}
		if (!is_writable($localFilename)) {
			throw new RuntimeException('Updated failed: file '.$localFilename.' could not be written');
		}
	}
}
