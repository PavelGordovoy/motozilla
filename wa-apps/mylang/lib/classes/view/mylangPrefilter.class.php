<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 28.03.2019
 * Time: 9:32
 */

class mylangPrefilter
{

}

/**
 * [`String`] and _w('String')
 * @param $matches
 * @return mixed|string|null
 * @throws waException
 */
function mylang_smarty_gettext_translate($matches)
{
    $locale = waConfig::get('mylang_locale');
    $custom = waConfig::get('mylang_custom_messages');
    if (!$custom) {
        $locale_path = waConfig::get('wa_path_root') . '/wa-data/protected/mylang/mylang_custom/locale';
        $po_locale = $locale_path . '/' .$locale.'/LC_MESSAGES/mylang_custom.po';
        if (file_exists($po_locale)) {
            $translations = mylangGettext::readPO($po_locale)->toArray();
            $data = ifset($translations, 'translations', []);
            $custom = array_column($data, 'translation','original');
        }
        waConfig::set('mylang_custom_messages', $custom);
    }
    $str = ifset($custom, $matches[1], null);
    if ($str) {
        if (is_array($str)) {
            $str = reset($str);
        }
        return $str;
    }

    if ($str = waLocale::getString($matches[1])) {
        return $str;
    }
    return _wp(str_replace('\"', '"', $matches[1]));
}

/**
 * @param $matches
 * @return string
 * @throws waException
 */
function mylang_smarty_gettext_s_translate($matches)
{
    return _ws(str_replace('\"', '"', $matches[1]));
}

/**
 * @param $source
 * @param $smarty
 * @return string|string[]|null
 */
function mylang_smarty_prefilter_translate($source, &$smarty)
{
    $mid_result = preg_replace_callback('/\[\`([^\`]+)\`\]/u', 'mylang_smarty_gettext_translate', $source);
    if ($mid_result === null) {
        return $source;
    }
    return preg_replace_callback("/\[s\`([^\`]+)\`\]/u", 'mylang_smarty_gettext_s_translate', $mid_result);
}

/**
 * @param $source
 * @param $smarty
 * @return string|string[]
 */
function mylang_smarty_prefilter_productsafterfeatures($source, &$smarty)
{
    return str_ireplace('{$features = $wa->shop->features($products)}', '{$features = $wa->shop->features($products)}{if $wa->mylang}{$products = $wa->mylang->products($products)}{/if}', $source);
}

