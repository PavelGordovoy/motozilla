<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformPluginsAction extends waViewAction
{

    public function execute()
    {
        if (!$this->getUser()->isAdmin($this->getApp())) {
            throw new waRightsException(_w("Permission denied"));
        }
        $this->getResponse()->setTitle(_w("Plugin settings"));
        // Sortable
        $version = wa()->getVersion();
        $this->getResponse()->addJs("wa-content/js/jquery-ui/jquery.ui.core.min.js?v=" . $version);
        $this->getResponse()->addJs("wa-content/js/jquery-ui/jquery.ui.widget.min.js?v=" . $version);
        $this->getResponse()->addJs("wa-content/js/jquery-ui/jquery.ui.mouse.min.js?v=" . $version);
        $this->getResponse()->addJs("wa-content/js/jquery-ui/jquery.ui.sortable.min.js?v=" . $version);

        $this->getResponse()->addJs("wa-content/js/jquery-plugins/jquery.history.js?v=" . $version);
        $this->getResponse()->addJs("wa-content/js/jquery-plugins/jquery.json.js?v=" . $version);
        $this->getResponse()->addJs("wa-content/js/jquery-plugins/jquery.store.js?v=" . $version);
        $this->getResponse()->addJs("wa-content/js/jquery-wa/wa.core.js?v=" . $version);

        $this->view->assign('plugins', wa()->getConfig()->getPlugins());
        $this->setLayout(new multiformBackendLayout());
    }

}
