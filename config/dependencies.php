<?php
/**
 * KohanaView dependency container configuration for use with https://github.com/zeelot/kohana-dependencies
 *
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2015 inGenerator Ltd
 * @license    http://kohanaframework.org/license
 */
return [
    'kohanaview' => [
        'renderer' => [
            'html' => [
                '_settings' => [
                    'class'     => '\Ingenerator\KohanaView\Renderer\HTMLRenderer',
                    'arguments' => ['%kohanaview.template.selector%', '%kohanaview.template.manager%'],
                    'shared'    => TRUE,
                ],
            ],
        ],
        'template' => [
            'compiler' => [
                '_settings' => [
                    'class'     => '\Ingenerator\KohanaView\TemplateCompiler',
                    'arguments' => [],
                    'shared'    => TRUE,
                ],
            ],
            'manager'  => [
                '_settings' => [
                    'class'     => '\Ingenerator\KohanaView\TemplateManager\CFSTemplateManager',
                    'arguments' => ['%kohanaview.template.compiler%', '@kohanaview.template_manager@'],
                    'shared'    => TRUE,
                ],
            ],
            'selector' => [
                '_settings' => [
                    'class'     => '\Ingenerator\KohanaView\ViewTemplateSelector',
                    'arguments' => [],
                    'shared'    => TRUE,
                ],
            ],
        ],
    ],
];
