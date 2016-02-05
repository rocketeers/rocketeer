<?php
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

require 'vendor/autoload.php';

$finder = Finder::create()->in(['bin', 'src', 'tests']);
$header = <<<EOF
This file is part of Rocketeer

(c) Maxime Fabre <ehtnam6@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return Config::create()
             ->setRiskyAllowed(true)
             ->setRules([
                 '@Symfony'                                  => true,
                 'header_comment'                            => ['header' => $header],
                 'ereg_to_preg'                              => true,
                 'linebreak_after_opening_tag'               => true,
                 'no_multiline_whitespace_before_semicolons' => true,
                 'no_php4_constructor'                       => true,
                 'no_short_echo_tag'                         => true,
                 'ordered_imports'                           => true,
                 'php_unit_construct'                        => true,
                 'php_unit_strict'                           => false,
                 'phpdoc_order'                              => true,
                 'phpdoc_property'                           => true,
                 'psr0'                                      => true,
                 'short_array_syntax'                        => true,
                 'strict'                                    => true,
                 'strict_param'                              => true,
             ])
             ->setUsingCache(true)
             ->finder($finder);
