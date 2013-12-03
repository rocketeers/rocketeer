<?php
namespace Rocketeer\Console;

use Herrera\Box\Compactor\Php;

class WhitespaceCompactor extends Php
{
	public function supports($file)
	{
		return $file !== 'src/config/config.php' and parent::supports($file);
	}
}
