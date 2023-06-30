<?php

/*Загружаем исходные данные в таблицу*/
$model = new waModel();

$data = array(
    array("name" => "widget", "title" => "Превью в виджете", "width" => 96, "height" => 0),
    array("name" => "preview", "title" => "Превью в списках", "width" => 150, "height" => 0),
    array("name" => "page", "title" => "Превью на странице", "width" => 400, "height" => 0)
);

foreach($data as $item){
    $model->query("REPLACE `blog_skimage_groups` (`name`, `title`, `width`, `height`) VALUES ('{$item["name"]}', '{$item["title"]}', {$item["width"]}, {$item["height"]})");
}
