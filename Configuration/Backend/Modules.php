<?php

declare(strict_types=1);


use HDNET\Importr\Controller\ImportrController;

return [
    'web_examples' => [
        'parent' => 'file',
        'access' => 'user,group',
        'path' => '/module/file/impotr',
        'labels' => 'LLL:EXT:importr/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'Importr',
        'controllerActions' => [
            ImportrController::class => [
                'index', 'import', 'preview', 'create', 'reset'
            ],
        ],
    ],
];
