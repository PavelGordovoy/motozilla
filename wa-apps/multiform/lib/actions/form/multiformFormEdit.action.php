<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormEditAction extends waViewAction
{

    public function execute()
    {
        $this->getResponse()->addJs('js/form/edit.js', 'multiform');

        $mf = new multiformFormModel();
        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        // Если ID формы не передан, то создаем новую форму
        if (!$id) {
            if (!wa()->getUser()->isAdmin('multiform') && !wa()->getUser()->getRights('multiform', 'create')) {
                throw new waRightsException('Access denied');
            }
            $id = $mf->createForm();
            $this->redirect(array("url" => "?module=form&action=edit&id=" . $id));
        } else {
            $form = $mf->getFormSettings($id);
            if (!multiformHelper::hasFullFormAccess($form) && !wa()->getUser()->getRights("multiform", "forms.{$form['id']}")) {
                throw new waRightsException('Access denied');
            }
            $this->view->assign("id", $id);
            $this->view->assign("form", $form);
            $this->view->assign("domain", wa('multiform')->getConfig()->getDomain());
            $this->setLayout(new multiformBackendLayout());
        }
    }

}
