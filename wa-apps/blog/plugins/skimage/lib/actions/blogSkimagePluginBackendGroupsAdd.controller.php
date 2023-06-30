<?php

class blogSkimagePluginBackendGroupsAddController extends waJsonController{

    public function execute(){

        $plugin_id = "skimage";

        $max_id = (int)waRequest::post("max_id");

        if(!$max_id){
            $max_id = 1;
        }

        $view = wa()->getView();
        $path = wa()->getAppPath() . "/plugins/{$plugin_id}";
        $view->assign("blog_skimage_id", $max_id);
        $html = $view->fetch($path . '/templates/actions/settings/SettingsGroupsRow.html');

        $this->response["html"] = $html;

        return true;

    }

}