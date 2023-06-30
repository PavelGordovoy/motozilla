<?php

class blogSkimageDataModel extends waModel{

    protected $table = 'blog_skimage_data';

    public function getAllImages(){

        $data = $this->getAll();

        $dataReturn = array();

        $cdn_url = "";

        $cdn = new waCdn();

        if(!empty($cdn->count())){
            $cdn_url = $cdn->getRandom();
        }

        if(!empty($data)){
            foreach($data as $item){
                $dataReturn[$item["post_id"]][$item["group_name"]] = $cdn_url . wa()->getDataUrl("skimage/{$item["post_id"]}/", true, 'blog') . $item["name"];
                if(!empty($item["query"])){
                    $dataReturn[$item["post_id"]][$item["group_name"]] .= "?" . $item["query"];
                }
            }
        }

        return $dataReturn;

    }

}
