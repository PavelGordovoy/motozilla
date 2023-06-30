<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageAppearanceAction extends waViewAction
{

    public function execute()
    {
        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        // Настройки формы
        $form_model = new multiformFormModel();
        $form_settings = $form_model->getFormSettings($id);

        if (!wa()->getUser()->isAdmin() && !wa()->getUser()->getRights('multiform', 'appearance')) {
            throw new waRightsException('Access denied');
        }

        // Все темы
        $mth = new multiformThemeModel();
        $themes = $mth->getThemes();

        $this->view->assign('id', $id);
        $this->view->assign('themes', $themes);
        $this->view->assign('form', $form_settings);
    }

}
