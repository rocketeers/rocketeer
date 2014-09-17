<?php
namespace Rocketeer\Console\Commands;

class IgniteCommand extends BaseTaskCommand
{
	/**
	 * Whether the command's task should be built
	 * into a pipeline or run straight
	 *
	 * @type boolean
	 */
	protected $straight = true;
}
