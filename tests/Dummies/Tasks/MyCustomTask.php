<?php
namespace Rocketeer\Dummies\Tasks;

use Rocketeer\Abstracts\AbstractTask;

class MyCustomTask extends AbstractTask
{
    public function execute()
    {
        return 'foobar';
    }
}
