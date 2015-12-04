<?php
use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;

$finder = DefaultFinder::create()->in(['bin', 'src', 'tests']);
$header = <<<EOF
This file is part of Rocketeer

(c) Maxime Fabre <ehtnam6@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return Config::create()
             ->setRiskyAllowed(true)
             ->setRules([
                 'header_comment'                    => ['header' => $header],
                 '@Symfony'                          => true,
                 'ereg_to_preg'                      => true,
                 'function_typehint_space'           => true,
                 'multiline_spaces_before_semicolon' => true,
                 'ordered_use'                       => true,
                 'php4_constructor'                  => true,
                 'php_unit_construct'                => true,
                 'php_unit_strict'                   => false,
                 'phpdoc_order'                      => true,
                 'phpdoc_types'                      => true,
                 'psr0'                              => true,
                 'short_array_syntax'                => true,
                 'short_echo_tag'                    => true,
                 'strict'                            => true,
                 'strict_param'                      => true,
             ])
             ->setUsingCache(true)
             ->finder($finder);
