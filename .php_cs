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
                 'ereg_to_preg',
                 'header_comment',
                 'multiline_spaces_before_semicolon',
                 'ordered_use',
                 'php4_constructor',
                 'phpdoc_order',
                 'short_array_syntax',
                 'short_echo_tag',
                 'strict',
                 'strict_param',
             ])
             ->setUsingCache(true)
             ->finder($finder);
