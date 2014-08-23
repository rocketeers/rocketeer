<?php
namespace Rocketeer\Dummies;

use Rocketeer\Plugins\AbstractNotifier;

class DummyBeforeAfterNotifier extends AbstractNotifier
{
	/**
	 * Send a given message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function send($message)
	{
		echo $message;

		return $message;
	}

	/**
	 * Get the default message format
	 *
	 * @param string $message The message handle
	 *
	 * @return string
	 */
	public function getMessageFormat($message)
	{
		return $message;
	}
}
