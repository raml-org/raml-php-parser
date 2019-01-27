<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests');

return PhpCsFixer\Config::create()
    ->setRules(
        [
            '@PSR2' => true,
            '@PHP56Migration' => true,
            '@PHP70Migration' => false,
            '@PHP71Migration' => false,
            '@PhpCsFixer' => true,
            'align_multiline_comment' => false,
            'array_syntax' => ['syntax' => 'short'],
            'backtick_to_shell_exec' => true,
            'blank_line_before_statement' => ['statements' => ['break', 'declare', 'continue', 'declare', 'die', 'do', 'exit', 'return', 'throw', 'try', 'while']],
            'cast_spaces' => ['space' => 'single'],
            'concat_space' => ['spacing' => 'one'],
            'date_time_immutable' => true,
            'declare_equal_normalize' => ['space' => 'none'],
            'heredoc_to_nowdoc' => false,
            'increment_style' => ['style' => 'post'],
            'linebreak_after_opening_tag' => true,
            'list_syntax' => ['syntax' => 'long'],
            'mb_str_functions' => false,
            'method_chaining_indentation' => true,
            'multiline_whitespace_before_semicolons' => false,
            'native_function_invocation' => ['include' => ['@all']],
            'no_superfluous_phpdoc_tags' => true,
            'non_printable_character' => false,
            'ordered_class_elements' => ['order' => ['use_trait', 'constant', 'property', 'construct', 'magic', 'method']],
            'php_unit_internal_class' => false,
            'php_unit_test_class_requires_covers' => false,
         //   'phpdoc_align' => ['align' => 'left'],
            'phpdoc_add_missing_param_annotation' => false,
            'phpdoc_align' => false,
            'phpdoc_annotation_without_dot' => false,
            'phpdoc_indent' => false,
            'phpdoc_inline_tag' => false,
            'phpdoc_no_access' => false,
            'phpdoc_no_alias_tag' => false,
            'phpdoc_no_empty_return' => false,
            'phpdoc_no_package' => false,
            'phpdoc_no_useless_inheritdoc' => false,
            'phpdoc_order' => false,
            'phpdoc_return_self_reference' => false,
            'phpdoc_scalar' => false,
            'phpdoc_separation' => false,
            'phpdoc_single_line_var_spacing' => false,
            'phpdoc_summary' => false,
            'phpdoc_to_comment' => false,
            'phpdoc_to_comment' => false,
            'phpdoc_to_return_type' => false,
            'phpdoc_trim' => false,
            'phpdoc_trim_consecutive_blank_line_separation' => false,
            'phpdoc_types' => false,
            'phpdoc_types_order' => false,
            'phpdoc_var_annotation_correct_order' => false,
            'phpdoc_var_without_name' => false,
         //   'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
            'phpdoc_types_order' => false,
            'simplified_null_return' => true,
            'single_line_comment_style' => false,
            'static_lambda' => true,
            'yoda_style' => false,
        ]
    )
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setIndent("    ")
    ->setLineEnding("\n")
    ->setFinder($finder);