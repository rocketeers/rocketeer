<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rocketeer\Console\Commands;

use Rocketeer\Abstracts\AbstractCommand;
use Symfony\Component\Console\Helper\Table;

/**
 * Lists the available options for each strategy
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
class StrategiesCommand extends AbstractCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'deploy:strategies';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Lists the available options for each strategy';

	/**
	 * Run the tasks
	 *
	 * @return void
	 */
	public function fire()
	{
		$strategies = array(
			'check'        => ['Php', 'Ruby', 'Node'],
			'deploy'       => ['Clone', 'Copy', 'Sync'],
			'test'         => ['Phpunit'],
			'migrate'      => ['Artisan'],
			'dependencies' => ['Composer', 'Bundler', 'Npm', 'Bower', 'Polyglot'],
		);

		$rows = [];
		foreach ($strategies as $strategy => $implementations) {
			foreach ($implementations as $implementation) {
				$instance = $this->laravel['rocketeer.builder']->buildStrategy($strategy, $implementation);
				$rows[] = [$strategy, $implementation, $instance->getDescription()];
			}
		}

		$this->table(['Strategy', 'Implementation', 'Description'], $rows);
	}
}
