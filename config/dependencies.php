<?php

declare(strict_types=1);

use Ingenerator\KohanaView\CoreTemplateCompiler;
use Ingenerator\KohanaView\Renderer\HTMLRenderer;
use Ingenerator\KohanaView\Renderer\PageLayoutRenderer;
use Ingenerator\KohanaView\TemplateManager\CFSTemplateManager;
use Ingenerator\KohanaView\ViewTemplateSelector;

/*
 * KohanaView dependency container configuration for use with https://github.com/zeelot/kohana-dependencies.
 */
return [
    'kohanaview' => [
        'renderer' => [
            'html' => [
                '_settings' => [
                    'class' => HTMLRenderer::class,
                    'arguments' => ['%kohanaview.template.selector%', '%kohanaview.template.manager%'],
                    'shared' => true,
                ],
            ],
            'page_layout' => [
                '_settings' => [
                    'class' => PageLayoutRenderer::class,
                    'arguments' => ['%kohanaview.renderer.html%', '%kohana.request%'],
                    'shared' => true,
                ],
            ],
        ],
        'template' => [
            'compiler' => [
                '_settings' => [
                    'class' => CoreTemplateCompiler::class,
                    'arguments' => [],
                    'shared' => true,
                ],
            ],
            'manager' => [
                '_settings' => [
                    'class' => CFSTemplateManager::class,
                    'arguments' => ['%kohanaview.template.compiler%', '@kohanaview.template_manager@'],
                    'shared' => true,
                ],
            ],
            'selector' => [
                '_settings' => [
                    'class' => ViewTemplateSelector::class,
                    'arguments' => [],
                    'shared' => true,
                ],
            ],
        ],
    ],
];
