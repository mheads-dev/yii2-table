<?php

$finder = PhpCsFixer\Finder::create()
	->in([
		__DIR__.'/src',
		__DIR__.'/tests',
	]);

return (new PhpCsFixer\Config())
	->setFinder($finder)
	->setRiskyAllowed(true)
	->setIndent("\t")
	->setRules([
		'@PER-CS'                    => true, // Последний стандарт кода принятый сообществом
		'@PER-CS:risky'              => true,
		'@PHP82Migration'            => true, // Что было добавлено в версии php 8.2
		'@PHP80Migration:risky'      => true,
		'@PHPUnit100Migration:risky' => true, // Правила для phpunit
		'no_unused_imports' => true,
		'ordered_imports'   => ['imports_order' => ['class', 'function', 'const']],
		'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
		'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
		'strict_comparison'      => false,
		'strict_param'           => false,
		'binary_operator_spaces' => [
			'operators' => [
				'=>' => 'align_single_space_minimal',
			],
		],
		'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
		'no_superfluous_elseif' => true,
		'no_useless_else'       => true,
		'no_useless_return'     => true,
		'php_unit_internal_class'                => true,
		'php_unit_construct'                     => true,
		'php_unit_fqcn_annotation'               => true,
		'php_unit_set_up_tear_down_visibility'   => true,
		'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
		'self_static_accessor' => true,
		'static_lambda' => true,
		'global_namespace_import' => true,
		'braces_position' => [
			'anonymous_classes_opening_brace'   => 'same_line',
			'anonymous_functions_opening_brace' => 'same_line',
			'classes_opening_brace'             => 'next_line_unless_newline_at_signature_end',
			'control_structures_opening_brace'  => 'next_line_unless_newline_at_signature_end',
			'functions_opening_brace'           => 'next_line_unless_newline_at_signature_end',
		],
		'control_structure_continuation_position' => [
			'position' => 'next_line',
		],
		'single_space_around_construct'           => false,
		'statement_indentation'                   => true,
		'single_blank_line_at_eof'    => false,
		'declare_strict_types'        => false,
		'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arrays', 'arguments', 'parameters']],
		'cast_spaces'                 => ['space' => 'none'],
	]);