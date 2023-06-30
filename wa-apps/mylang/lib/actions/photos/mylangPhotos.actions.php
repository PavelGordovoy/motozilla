<?php
class mylangPhotosActions extends waViewActions
{
    public function defaultAction()
    {
        $this->setLayout(new mylangBackendLayout());
    }
    
    public function tagsAction()
    {
        wa('photos');
        $model = new mylangModel();
        $t_model = new photosTagModel();
        $tags = $t_model->getAll('id');
        $loc_tag = $model->getByField('type', 'tag', true);
        foreach ($loc_tag as $locf) {
            if (isset($tags[$locf['type_id']])) {
                $tags[$locf['type_id']]['locale'][$locf['locale']] = $locf;
            }
        }
        $this->view->assign('tags', $tags);
        $this->view->assign('locales', mylangLocale::getAll('name'));
    }
}
