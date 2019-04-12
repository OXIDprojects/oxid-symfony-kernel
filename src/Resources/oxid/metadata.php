<?php

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

// 1234

/**
 * Module information
 */
$aModule = [
    'id' => 'SiowebOxidKernel',
    'title' => '<i></i><b style="color: #005ba9">Sioweb</b> | Oxid Kernel',
    'description' => '.',
    'version' => '1.0',
    'url' => 'https://www.sioweb.de',
    'email' => 'support@sioweb.com',
    'author' => 'Sascha Weidner',
    'extend' => [
        \OxidEsales\Eshop\Core\ShopControl::class =>
            Sioweb\Oxid\Kernel\Legacy\Core\ShopControl::class,
    ],
    'events' => [
        'onActivate' => '\Sioweb\Oxid\Kernel\Legacy\Core\Events::onActivate',
        'onDeactivate' => '\Sioweb\Oxid\Kernel\Legacy\Core\Events::onDeactivate',
    ],
    // 'templates' => [
    //     'formbuilder_shop_main.tpl' => 'sioweb/Backend/views/admin/tpl/form/formbuilder_shop_main.tpl',
    // ],
    // 'blocks' => [
    //     [
    //         'template' => 'headitem.tpl',
    //         'block' => 'admin_headitem_inccss',
    //         'file' => 'admin_headitem_inccss.tpl',
    //     ],
    // ]
];