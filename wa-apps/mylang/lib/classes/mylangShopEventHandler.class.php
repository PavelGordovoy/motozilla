<?php

class mylangShopEventHandler extends mylangEventHandler
{
    /**
     * @return bool|int
     */
    public static function checkVersion()
    {
        static $shopVersion;
        if (!$shopVersion) {
            try {
                $shopVersion = wa()->getVersion('shop');
            } catch (waException $e) {
                $shopVersion = '7.0';
            }
        }
        return version_compare('7.5', $shopVersion,  '>');
    }
}