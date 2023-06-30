<?php

class blogSkimagePlugin extends blogPlugin{

    public $pluginID = "skimage";

    public function addInputsFile($post){

        $plugin_id = $this->pluginID;

        if(!isset($post["id"]) || empty($post["id"])){
            return "";
        }

        $settings = wa("blog")->getPlugin($plugin_id)->getSettings();

        if(!$settings["status"]){
            return "";
        }

        $groupsModel = new blogSkimageGroupsModel();

        $groups = $groupsModel->getAll();

        if(empty($groups)){
            return "";
        }

        $dataModel = new blogSkimageDataModel();
        $data = $dataModel->query("SELECT * FROM blog_skimage_data WHERE post_id = {$post["id"]}")->fetchAll();
        $dataImages = array();
        if(!empty($data)){
            foreach($data as $item){
                $dataImages[$item["group_name"]] = $item;
                $dataImages[$item["group_name"]]["url"] = wa()->getDataUrl("skimage/{$post["id"]}/", true, 'blog') . $item["name"];
            }
        }

        $view = wa()->getView();

        $view->assign("blog_skimage_groups", $groups);
        $view->assign("blog_skimage_data", $dataImages);

        $init = array(
            "url" => wa()->getAppUrl('blog'),
            "post_id" => $post["id"]
        );
        $view->assign("blog_skimage_init", $init);

        $path = wa()->getAppPath() . "/plugins/{$plugin_id}/templates/actions/backend/";
        $view->assign("blog_skimage_path", $path);

        $result["toolbar"] = $view->fetch($path . "inputsCategory.html");

        return $result;

    }

    public static function clearCache(){

        $cache = new waSerializeCache('blogSkimage', 3600, 'blog/skimage');
        $cache->delete();

    }

}
