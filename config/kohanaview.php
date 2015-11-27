<?php
/**
 * Configuration for the KohanaView module
 *
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
return [
    'template_manager' => [
        // Where compiled templates should be stored
        'cache_dir'        => \Kohana::$cache_dir.'/compiled_templates',

        // Whether to recompile all templates on the first use of that template even if it exists
        'recompile_always' => (\Kohana::$environment === \Kohana::DEVELOPMENT),
    ],
];
