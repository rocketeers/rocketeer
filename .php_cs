<?php
require 'vendor/autoload.php';

return Madewithlove\PhpCsFixer\Config::fromFolders([
    'bin',
    'src/Rocketeer',
    'tests',
])->mergeRules([
    'header_comment' => ['header' => file_get_contents(__DIR__.'/bin/banner.txt')],
    'ternary_to_null_coalescing' => false,
]);
