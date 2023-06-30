<?php

/**
 * @author Плагины Вебасист <info@wa-apps.ru>
 * @link http://wa-apps.ru/
 */
return array(
    'name' => /*_wp*/('Motos'),
    'description' => /*_wp*/('Storefront’s product filtering by moto (manufacturer). You can upload image and add description for the moto.'),
    'version' => '2.6',
    'vendor' => 809114,
    'img'=>'img/motos.png',
    'shop_settings' => true,
    'frontend'    => true,
    'icons'=>array(
        16 => 'img/motos.png',
    ),
    'handlers' => array(
        'frontend_nav' => 'frontendNav',
        'frontend_nav_aux' => 'frontendNavAux',
        'backend_products' => 'backendProducts',
        'products_collection' => 'productsCollection',
        'sitemap' => 'sitemap'
    ),
);

