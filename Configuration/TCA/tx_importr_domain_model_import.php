<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:tx_importr_domain_model_import',
        'label' => 'starttime',
        'label_alt' => 'filepath',
        'label_alt_force' => 1,
        'searchFields' => 'filepath',
        'rootLevel' => 1,
        'default_sortby' => 'ORDER BY starttime',
        'delete' => 'deleted',
        'iconfile' => 'EXT:importr/Resources/Public/Icons/Import.png',
    ],
    'types' => [
        '1' => ['showitem' => 'strategy,filepath,starttime,endtime,pointer,amount'],
    ],
    'columns' => [
        'starttime' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
            ],
        ],
        'endtime' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
            ],
        ],
        'pointer' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:pointer',
            'config' => [
                'type' => 'none',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'amount' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:amount',
            'config' => [
                'type' => 'none',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'errors' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:error',
            'config' => [
                'type' => 'none',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'inserted' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:error',
            'config' => [
                'type' => 'none',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'updated' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:error',
            'config' => [
                'type' => 'none',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'ignored' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:error',
            'config' => [
                'type' => 'none',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'unknowns' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:error',
            'config' => [
                'type' => 'none',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'filepath' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:filepath',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['file'],
            ],
        ],
        'strategy' => [
            'label' => 'LLL:EXT:importr/Resources/Private/Language/locallang.xlf:strategy',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_importr_domain_model_strategy',
                'maxitems' => 1,
                'size' => 1,
            ],
        ],
    ],
];
