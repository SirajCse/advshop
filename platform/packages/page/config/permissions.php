<?php

return [
    [
        'name' => 'Pages',
        'flag' => 'pages.index',
        'parent_flag' => 'core.cms',
    ],
    [
        'name' => 'Create',
        'flag' => 'pages.create',
        'parent_flag' => 'pages.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'pages.edit',
        'parent_flag' => 'pages.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'pages.destroy',
        'parent_flag' => 'pages.index',
    ],
    [
        'name' => 'Export Pages',
        'flag' => 'pages.export',
        'parent_flag' => 'tools.data-synchronize',
    ],
    [
        'name' => 'Import Pages',
        'flag' => 'pages.import',
        'parent_flag' => 'tools.data-synchronize',
    ],
];
