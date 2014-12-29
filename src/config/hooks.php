<?php return array(

    // Tasks
    //
    // Here you can define in the `before` and `after` array, Tasks to execute
    // before or after the core Rocketeer Tasks. You can either put a simple command,
    // a closure which receives a $task object, or the name of a class extending
    // the Rocketeer\Abstracts\AbstractTask class
    //
    // In the `custom` array you can list custom Tasks classes to be added
    // to Rocketeer. Those will then be available in the command line
    // with all the other tasks
    //////////////////////////////////////////////////////////////////////

    // Tasks to execute before the core Rocketeer Tasks
    'before' => array(
        'setup'   => array(),
        'deploy'  => array(),
        'cleanup' => array(),
    ),

    // Tasks to execute after the core Rocketeer Tasks
    'after'  => array(
        'setup'   => array(),
        'deploy'  => array(),
        'cleanup' => array(),
    ),

    // Custom Tasks to register with Rocketeer
    'custom' => array(),

);
