<?php
namespace Rocketeer\Dummies\Tasks;

use Rocketeer\Tasks\AbstractTask;

class DummyShelledTask extends AbstractTask
{
    /**
     * @var array
     */
    protected $options = [
        'shelled' => true,
    ];

    /**
     * Run the task.
     *
     * @return mixed
     */
    public function execute()
    {
        return $this->run([
            'echo "foo"',
            'echo "bar"',
        ]);
    }
}
