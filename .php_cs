<?php
use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\Fixer\Contrib\HeaderCommentFixer;
use Symfony\CS\FixerInterface;

$finder = DefaultFinder::create()->in(['bin', 'src', 'tests']);
$header = <<< EOF
This file is part of Rocketeer

(c) Maxime Fabre <ehtnam6@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

HeaderCommentFixer::setHeader($header);

return Config::create()
             ->level(FixerInterface::SYMFONY_LEVEL)
             ->fixers([
                 '-yoda_conditions',
                 'align_double_arrow',
                 'align_equals',
                 // 'concat_with_spaces',
                 'ereg_to_preg',
                 'header_comment',
                 'multiline_spaces_before_semicolon',
                 'no_blank_lines_before_namespace',
                 'ordered_use',
                 // 'php4_constructor',
                 'phpdoc_order',
                 'phpdoc_var_to_type',
                 'short_array_syntax',
                 'strict',
                 'strict_param',
             ])
             ->setUsingCache(true)
             ->finder($finder);
