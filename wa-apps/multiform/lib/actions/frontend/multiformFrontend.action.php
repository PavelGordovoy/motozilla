<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFrontendAction extends waViewAction
{

    public function execute()
    {
        $url = waRequest::param("url");
        if (substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }
        $form_model = new multiformFormModel();
        if ($id = $form_model->getIdByUrl($url)) {
            $form = $form_model->getFullForm($id);

            // Если форма не общедоступна
            if (!$form['type'] && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$form['id']}") <= 1) {
                $form = array();
            }

            $this->getResponse()->setTitle($form['meta_title']);
            $this->getResponse()->setMeta('keywords', $form['meta_keywords']);
            $this->getResponse()->setMeta('description', $form['meta_description']);

            $this->view->assign("form", $form);
        } else {
            throw new waException('Form not found', 404);
        }
    }

}
