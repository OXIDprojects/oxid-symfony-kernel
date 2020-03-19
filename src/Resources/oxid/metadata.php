<?php

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

// 12345

/**
 * Module information
 */
$aModule = [
    'id' => 'OxidCommunitySymfonyKernel',
    'title' => 'Symfony Kernel',
    'description' => '.',
    'version' => '1.0',
    'url' => 'https://github.com/OXIDprojects/oxid-symfony-kernel',
    'author' => 'Sascha Weidner',
    'extend' => [
        \OxidEsales\Eshop\Core\ShopControl::class =>
            OxidCommunity\SymfonyKernel\Legacy\Core\ShopControl::class,
        \OxidEsales\Eshop\Core\SeoDecoder::class =>
            OxidCommunity\SymfonyKernel\Legacy\Core\SeoDecoder::class,
        \OxidEsales\Eshop\Core\ViewConfig::class =>
            OxidCommunity\SymfonyKernel\Legacy\Core\ViewConfig::class,
    ],
    'events' => [
        'onActivate' => '\OxidCommunity\SymfonyKernel\Legacy\Core\Events::onActivate',
        'onDeactivate' => '\OxidCommunity\SymfonyKernel\Legacy\Core\Events::onDeactivate',
    ]
];