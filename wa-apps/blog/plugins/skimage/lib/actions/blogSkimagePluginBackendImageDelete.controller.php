<?php

class blogSkimagePluginBackendImageDeleteController extends waJsonController{

    public function execute(){

        $plugin_id = "skimage";

        $group_name = waRequest::post("group_name");
        $post_id = waRequest::post("post_id");

        if(!$group_name || !$post_id){
            return false;
        }

        $dataModel = new blogSkimageDataModel();
        $data = $dataModel->getByField(array("group_name" => $group_name, "post_id" => $post_id));
        $dataModel->deleteByField(array("group_name" => $group_name, "post_id" => $post_id));

        $publicPath = wa()->getDataPath("skimage/{$post_id}/", true, 'blog');
        $protectedPath = wa()->getDataPath("skimage/{$post_id}/", false, 'blog');

        $name_2x = blogSkimageResize::getName2X($data["name"]);

        waFiles::delete($publicPath . $data["name"]);
        waFiles::delete($publicPath . $name_2x);
        waFiles::delete($protectedPath . $data["name"]);

        $view = wa()->getView();
        $path = wa()->getAppPath() . "/plugins/{$plugin_id}/templates/actions/backend/";

        $groupsModel = new blogSkimageGroupsModel();
        $group = $groupsModel->getByField("name", $group_name);
        $view->assign("group", $group);
        $data["html"] = $view->fetch($path . "inputsCategoryRowEmpty.html");

        blogSkimagePlugin::clearCache();

        $this->response = $data;

        return true;

    }

}