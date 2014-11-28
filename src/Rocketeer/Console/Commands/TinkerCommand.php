<?php
namespace Rocketeer\Console\Commands;

use Boris\Boris;
use Illuminate\Console\Command;
use Rocketeer\Abstracts\AbstractCommand;

class TinkerCommand extends AbstractCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'tinker';

	/**
	 * @type string
	 */
	protected $description = "Debug Rocketeer's environment";

	/**
	 * Fire the command
	 */
	public function fire()
	{
		$boris = new Boris('rocketeer> ');
		$boris->setLocal(array(
			'rocketeer' => $this->laravel,
		));

		$boris->start();
	}
}
