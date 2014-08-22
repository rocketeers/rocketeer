<?php
namespace Rocketeer\Dummies;

use Rocketeer\Plugins\AbstractNotifier;

class DummyNotifier extends AbstractNotifier
{
	/**
	 * Get the default message format
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	public function getMessageFormat($message)
	{
		return '{1} finished deploying branch "{2}" on "{3}" ({4})';
	}

	/**
	 * Send a given message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function send($message)
	{
		print $message;

		return $message;
	}
}
