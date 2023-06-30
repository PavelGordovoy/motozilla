<?php

/*
 * This file is part of Webasyst framework.
 *
 * Licensed under the terms of the GNU Lesser General Public License (LGPL).
 * http://www.webasyst.com/framework/license/
 *
 * @link http://www.webasyst.com/
 * @author Webasyst LLC
 * @copyright 2011 Webasyst LLC
 * @package wa-system
 * @subpackage workflow
 */

class waWorkflowAction extends waWorkflowEntity
{
    /**
     * @var waSmarty3View
     */
    protected $view;

    /**
      * Interface function to perform this action.
      * Calls preExecute(), execute() and postExecute() internally.
      * @param mixed $params implementation-specific parameters
      * @return mixed result of $this->execute(), or null if the call was canceled by $this->preExecute()
      */
    public function run($params = null)
    {
        if (!$this->preExecute($params)) {
            return null;
        }
        if (null !== ( $result = $this->execute($params))) {
            return $this->postExecute($params, $result);
        }
        return null;
    }

    /**
      * Called by $this->run() before $this->execute().
      * May cancel the call to $this->execute() by returning false.
      * @param mixed $params implementation-specific parameter passed to $this->run()
      * @return boolean false to abort the action; true to proceed to $this->execute().
      */
    protected function preExecute($params = null)
    {
        // override in subclasses...
        return true;
    }

    /**
     * Does all the actual work this action needs to do.
     * (!!! declared as public for historical reasons only)
     * @param mixed $params implementation-specific parameter passed to $this->run()
     * @return mixed null if this action failed; any data to pass to $this->postExecute() if completed successfully.
     */
    public function execute($params = null)
    {
        // override in subclasses...
        return null;
    }

    /**
     * Called by $this->run() if $this->execute() returned non-null value
     * @TODO: make protected (declared as public for historical reasons only)
     * @param mixed $params implementation-specific parameter passed to $this->run()
     * @param mixed $result return value of $this->execute()
     * @return mixed
     */
    public function postExecute($params = null, $result = null)
    {
        // override in subclasses...
        return $result;
    }

    /**
     * Initialize $this->view (unless already initialized) and return it.
     * @return waSmarty3View
     */
    protected function getView()
    {
        if (!$this->view) {
            $this->view = waSystem::getInstance()->getView();
            $this->view->assign('action', $this);
        }
        return $this->view;
    }

    /**
     * @param string $template suffix to add to template basename
     * @return string template file basename for this action. Can be overriden in subclasses.
     */
    protected function getTemplateBasename($template='')
    {
        return get_class($this).($template ? '_'.$template : '').$this->getView()->getPostfix();
    }

    /**
     * @return string dir to look template files in (path with trailing slash)
     */
    protected function getTemplateDir()
    {
        return $this->workflow->getPath('/actions/templates/');
    }

    /**
     * Fetch the HTML generated by view $this->getView() using template file
     * current_workflow_dir/actions/templates/$this->getTemplateBasename($template)
     *
     * @param string $template suffix to add to template basename (before smarty extension).
     * @return string
     */
    protected function display($template = '')
    {
        $dir = $this->getTemplateDir();
        $basename = $this->getTemplateBasename($template);
        return $this->getView()->fetch($dir.$basename);
    }
}

