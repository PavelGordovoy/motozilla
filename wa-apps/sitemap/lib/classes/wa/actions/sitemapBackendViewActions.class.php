<?php

class sitemapBackendViewActions extends waViewActions
{
	/** @var sitemapBackendLayout */
	protected $layout;

	protected function preExecute()
	{
		$layout = new sitemapBackendLayout();
		$this->setLayout($layout);

		$this->setTemplate('Default');
	}

	public function execute($action, $params = null)
	{
		$method = $action . 'Action';
		if (method_exists($this, $method))
		{
			/** @var sitemapBackendViewAction $view_action */
			$view_action = $this->$method($params);
			$this->executeViewAction($view_action);
		}
		else
		{
			throw new waException('Action ' . $method . ' not found', 404);
		}
	}

	protected function addJs($relative_path)
	{
		$this->getResponse()->addJs($relative_path, 'sitemap');
	}

	protected function addCss($relative_path)
	{
		$this->getResponse()->addCss($relative_path, 'sitemap');
	}

	private function executeViewAction(sitemapBackendViewAction $action)
	{
		$this->layout->setActiveMenuElement($action->activeMenuElement());

		$this->view->assign('action_state', $action->execute(waRequest::request()));
	}
}