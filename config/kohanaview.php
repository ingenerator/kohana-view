<?php

/**
 * Configuration for the KohanaView module.
 */
return [
    'template_manager' => [
        // Where compiled templates should be stored
        'cache_dir' => Kohana::$cache_dir.'/compiled_templates',

        // Whether to recompile all templates on the first use of that template even if it exists
        'recompile_always' => (Kohana::$environment === Kohana::DEVELOPMENT),
    ],
];
