<?php

/**
 * Класс Хелпер для запроса изображений
 */
class blogSkimageHelper{

    static public $all = null;

    public static function getImages($post_id = null, $group_name = null){

        $plugin_id = "skimage";

        $settings = wa("blog")->getPlugin($plugin_id)->getSettings();

        if(!$settings["status"]){
            return array();
        }

        $data = self::$all;
        $cache = null;

        if(empty($data)){

            if($settings["is_cache"]){
                $cache = new waSerializeCache('blogSkimage', 3600, 'blog/skimage');
                if ($cache && $cache->isCached()) {
                    $data = $cache->get();
                }
            }

            if(empty($data)){

                $dataModel = new blogSkimageDataModel();
                $data = $dataModel->getAllImages();
                if($settings["is_cache"] && ifset($cache)){
                    $cache->set($data);
                }

            }

            self::$all = $data;

        }

        if(!empty($data) && $post_id){
            if(isset($data[$post_id])){
                $data = $data[$post_id];
                if($group_name){
                    if(isset($data[$group_name])){
                        $data = $data[$group_name];
                    }else{
                        $data = "";
                    }
                }
            }else{
                $data = array();
            }
        }

        return $data;

    }

}