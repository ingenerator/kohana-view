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
                    'class' => '\Ingenerator\KohanaView\Renderer\HTMLRenderer',
                    'arguments' => ['%kohanaview.template.selector%', '%kohanaview.template.manager%'],
                    'shared' => true,
                ],
            ],
            'page_layout' => [
                '_settings' => [
                    'class' => Ingenerator\KohanaView\Renderer\PageLayoutRenderer::class,
                    'arguments' => ['%kohanaview.renderer.html%', '%kohana.request%'],
                    'shared' => true,
                ],
            ],
        ],
        'template' => [
            'compiler' => [
                '_settings' => [
                    'class' => '\Ingenerator\KohanaView\TemplateCompiler',
                    'arguments' => [],
                    'shared' => true,
                ],
            ],
            'manager' => [
                '_settings' => [
                    'class' => '\Ingenerator\KohanaView\TemplateManager\CFSTemplateManager',
                    'arguments' => ['%kohanaview.template.compiler%', '@kohanaview.template_manager@'],
                    'shared' => true,
                ],
            ],
            'selector' => [
                '_settings' => [
                    'class' => '\Ingenerator\KohanaView\ViewTemplateSelector',
                    'arguments' => [],
                    'shared' => true,
                ],
            ],
        ],
    ],
];
