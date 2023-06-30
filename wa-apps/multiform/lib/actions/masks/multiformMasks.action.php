<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformMasksAction extends waViewAction
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin('multiform')) {
            throw new waRightsException('Access denied');
        }

        $multiform_mask = new multiformMaskModel();
        $masks = $multiform_mask->getMasks();

        $this->view->assign('masks', $masks);

        $this->setLayout(new multiformBackendLayout());
    }

}
