<?php


class shopGoodtoysHelper
{

    public static function getPageUrl($page)
    {
        $url = wa()->getConfig()->getCurrentUrl();

        $get_params = array();
        if (($i = strpos($url, '?')) !== false) {
            parse_str(substr($url, $i+1), $get_params);

            $url = substr($url, 0, $i);
        }

        if($page > 1) {
            $get_params['page'] = $page;
        } elseif (isset($get_params['page'])) {
            unset($get_params['page']);
        }

        if (isset($get_params[$url])) {
            unset($get_params[$url]);
        }

        $url_params = http_build_query($get_params);
        return wa()->getConfig()->getHostUrl().$url.($url_params ? '?'.$url_params : '');
    }
}