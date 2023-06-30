<?php
class shopPashok
{

    public static function getContactCategories($contact_id)
    {
        $ccm = new waContactCategoriesModel();
        return $ccm->getContactCategories($contact_id);
    }

    public static function getContactCategoryIds($contact_id)
    {
        return array_keys(self::getContactCategories($contact_id));
    }

}