<?php
namespace Rocketeer\Console;

use Herrera\Box\Compactor\Php;

class WhitespaceCompactor extends Php
{
	/**
	 * Whether this compactor supports a given file
	 *
	 * @param string $file
	 *
	 * @return boolean
	 */
	public function supports($file)
	{
		return $file !== 'src/config/config.php' and parent::supports($file);
	}
}
