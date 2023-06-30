<?php

class blogSkimagePluginBackendImageSaveController extends waJsonController{

    public function execute(){

        $plugin_id = "skimage";

        $group_name = waRequest::post("group_name");
        $post_id = waRequest::post("post_id");

        $settings = wa("blog")->getPlugin($plugin_id)->getSettings();

        if(!$group_name || !$post_id){
            return false;
        }

        $file = waRequest::file($group_name);

        if(!$file->uploaded()){
            return false;
        }

        try{

            $groupsModel = new blogSkimageGroupsModel();
            $group = $groupsModel->getByField("name", $group_name);
            $group["width"] = (int)$group["width"];
            $group["height"] = (int)$group["height"];
            $realname = $file->name;
            $filename = $group_name . "_" . $realname;

            $publicPath = wa()->getDataPath("skimage/{$post_id}/", true, 'blog');
            $protectedPath = wa()->getDataPath("skimage/{$post_id}/", false, 'blog');

            if($file->type == "image/svg+xml"){

                $file->copyTo($protectedPath, $filename);
                $file->moveTo($publicPath, $filename);

            }else{

                $image = $file->waImage();

                $image->save($protectedPath . $filename);

                if($group["width"] || $group["height"]){
                    $image = blogSkimageResize::generateThumb($protectedPath . $filename, $group["width"], $group["height"]);
                }

                $image->save($publicPath . $filename);

                if((int)$settings["is_2x"]){

                    $filename_2x = blogSkimageResize::getName2X($filename);
                    $group_2x = array();

                    if($group["width"] || $group["height"]){
                        $group_2x["width"] = $group["width"] * 2;
                        $group_2x["height"] = $group["height"] * 2;
                        $image = blogSkimageResize::generateThumb($protectedPath . $filename, $group_2x["width"], $group_2x["height"]);
                    }

                    $image->save($publicPath . $filename_2x);
                }
            }

            $dataModel = new blogSkimageDataModel();

            $data = array(
                "group_name" => $group_name,
                "post_id" => $post_id,
                "name" => $filename,
                "type" => $file->type,
                "size" => $file->size,
                "query" => "",
            );

            if((int)$settings["query_string"]){
                $data["query"] = time();
            }

            $data["id"] = $dataModel->replace($data);
            $data["url"] = wa()->getDataUrl("skimage/{$post_id}/", true, 'blog') . $data["name"];

            $view = wa()->getView();
            $path = wa()->getAppPath() . "/plugins/{$plugin_id}/templates/actions/backend/";
            $view->assign("data", $data);
            $view->assign("group", $group);
            $data["html"] = $view->fetch($path . "inputsCategoryRowLoad.html");

            blogSkimagePlugin::clearCache();

            $this->response = $data;

            return true;

        }catch(waException $e){

            $this->errors[] = "При загрузке изображения {$realname} произошла ошибка. Проверьте корректность файла";

            return false;

        }

    }

}