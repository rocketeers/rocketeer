<?php
namespace Rocketeer\Dummies\Plugins;

use Rocketeer\Abstracts\AbstractPlugin;

class DummyBuilderPlugin extends AbstractPlugin
{
    /**
     * @type array
     */
    protected $lookups = array(
        'tasks' => 'Rocketeer\Dummies\Tasks\%s',
    );
}
