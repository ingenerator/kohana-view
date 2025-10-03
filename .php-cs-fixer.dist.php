<?php


use PhpCsFixer\Finder;
use PhpCsFixer\Config;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;

$finder = new Finder()
    ->in(__DIR__);

return new Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        // @todo: Bump to php84 migration when we drop 8.2 support
        '@PHP82Migration' => true,
        '@PHP82Migration:risky' => true,
        '@PHPUnit9x1Migration:risky' => true,
        // --- Initial set taken from Behat/Behat
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'concat_space' => false, // override Symfony
        'global_namespace_import' => [ //override Symfony
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'phpdoc_separation' => [ // override Symfony
            'groups' => [
                ['Annotation', 'NamedArgumentConstructor', 'Target'],
                ['Given', 'When', 'Then'],
                ...PhpdocSeparationFixer::OPTION_GROUPS_DEFAULT,
            ],
        ],
        'single_line_throw' => false, //override Symfony
        'yoda_style' => false, //override Symfony
        // --- Custom inGenerator overrides
        'not_operator_with_space' => true, // ensure space either side of the `!` operator
        'phpdoc_no_alias_tag' => false, // Don't convert `@property-read` to `@property`
        'phpdoc_to_comment' => [ // Keep as a phpdoc if it contains the `@var` annotation for phpstorm
            'ignored_tags' => ['var'],
        ],
        'no_alternative_syntax' => [
            'fix_non_monolithic_code' => false, // Allow use of the if(): etc syntax if the file also contains html
        ],
        'echo_tag_syntax' => false, // Still want to use <?= in our view templates
        'general_phpdoc_annotation_remove' => [ // Our author, licence etc tags are always internal to us... I think
            'annotations' => ['category', 'author', 'licence', 'license', 'copyright'],
        ],
        'php_unit_method_casing' => false, // We have some testcases that extend vendor ones with snake_case names
        'phpdoc_align' => [ // Override Behat and symfony, force our phpdoc back to being non-aligned
            'align' => 'left',
            'tags' => [
                'method',
                'param',
                'property',
                'property-read',
                'return',
                'throws',
                'type',
                'var',
            ],
        ],
        // -- Disabled until we go to 5.x
        'void_return' => false,
        'declare_strict_types' => false,
    ])
    ->setFinder($finder);
