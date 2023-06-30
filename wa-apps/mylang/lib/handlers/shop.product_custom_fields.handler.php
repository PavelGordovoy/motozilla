<?php

class mylangShopProduct_custom_fieldsHandler extends mylangShopEventHandler
{
    public function execute(&$params)
    {
        $fields = [];
        if ($this->checkVersion()) {
            return $fields;
        }
        //TODO translate _wp
        $locales = mylangLocale::getAll();

        foreach ($locales as $l) {
            $fields['product']['name/' . $l] = "Наименование товара (" . $l . ")";
            $fields['product']['summary/' . $l] = "Краткое описание (" . $l . ")";
            $fields['product']['description/' . $l] = "Описание (" . $l . ")";
            $fields['product']['meta_title/' . $l] = "Заголовок (" . $l . ")";
            $fields['product']['meta_keywords/' . $l] = "META Keywords (" . $l . ")";
            $fields['product']['meta_description/' . $l] = "META Description (" . $l . ")";
            $fields['sku']['skuname/' . $l] = "Наименование артикула (" . $l . ")";   // no skus. no life
        }
        
        return $fields;
    }
}
