<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Tasks;

/**
 * Installs or update the dependencies on server.
 */
class Dependencies extends AbstractTask
{
    /**
     * A description of what the task does.
     *
     * @var string
     */
    protected $description = 'Installs or update the dependencies on server';

    /**
     * @var array
     */
    protected $options = [
        'shared_dependencies' => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $method = $this->getOption('update', true) ? 'update' : 'install';

        return $this->executeStrategyMethod('Dependencies', $method);
    }
}
