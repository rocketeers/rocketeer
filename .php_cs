<?php
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

require 'vendor/autoload.php';

$header = file_get_contents(__DIR__.'/bin/banner.txt');
$finder = Finder::create()->in([
    'bin',
    'src',
    'tests',
]);

return Config::create()
             ->setRiskyAllowed(true)
             ->setRules([
                 '@Symfony' => true,
                 'align_double_arrow' => false,
                 'align_equals' => false,
                 'combine_consecutive_unsets' => true,
                 'concat_with_spaces' => false,
                 'dir_constant' => true,
                 'echo_to_print' => false,
                 'ereg_to_preg' => true,
                 'general_phpdoc_annotation_remove' => false,
                 'general_phpdoc_annotation_rename' => false,
                 'header_comment' => ['header' => $header],
                 'linebreak_after_opening_tag' => true,
                 'long_array_syntax' => false,
                 'modernize_types_casting' => true,
                 'no_blank_lines_before_namespace' => false,
                 'no_multiline_whitespace_before_semicolons' => true,
                 'no_php4_constructor' => true,
                 'no_short_echo_tag' => true,
                 'no_useless_else' => true,
                 'no_useless_return' => true,
                 'not_operator_with_space' => false,
                 'not_operator_with_successor_space' => false,
                 'ordered_class_elements' => false,
                 'ordered_imports' => true,
                 'php_unit_construct' => true,
                 'php_unit_dedicate_assert' => true,
                 'php_unit_strict' => false,
                 'phpdoc_order' => true,
                 'phpdoc_property' => true,
                 'phpdoc_var_to_type' => false,
                 'psr0' => true,
                 'psr4' => true,
                 'random_api_migration' => true,
                 'short_array_syntax' => true,
                 'strict_comparison' => true,
                 'strict_param' => true,
             ])
             ->finder($finder);
