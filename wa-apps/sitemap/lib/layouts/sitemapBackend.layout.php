<?php

class sitemapBackendLayout extends waLayout
{
	protected $state;

	public function __construct()
	{
		parent::__construct();

		$backend_url = wa('sitemap')->getAppUrl('sitemap');

		$this->state = array(
			'navigation' => array(
				sitemapSitemapStructureViewAction::MENU_ID => array(
					'icon' => 'view-table',
					'title' => 'Структура',
					'url' => $backend_url,
				),
				sitemapGeneralSettingsViewAction::MENU_ID => array(
					'icon' => 'settings',
					'title' => 'Настройки',
					'url' => $backend_url . '?action=generalSettings',
				),
			),
			'without_layout' => waRequest::request('without_layout') === '1',
		);
	}

	public function execute()
	{
		$this->view->assign(array(
			'page' => $this->getPage(),
			'assets_path' => wa()->getAppStaticUrl('sitemap' . '/assets'),
			'layout_state' => $this->getStateObject(),
		));
	}

	public function getStateObject()
	{
		return $this->state;
	}

	public function setActiveMenuElement($element)
	{
		if (isset($this->state['navigation'][$element]))
		{
			foreach ($this->state['navigation'] as &$menu_options)
			{
				$menu_options['is_active'] = false;
			}
			unset($menu_options);

			$this->state['navigation'][$element]['is_active'] = true;
		}
	}

	protected function getPage()
	{
		$module = waRequest::get('module', 'backend');
		$page = waRequest::get('action', 'default');
		if ($module != 'backend') {
			$page = $module.':'.$page;
		}
		$plugin = waRequest::get('plugin');
		if ($plugin) {
			if ($module == 'backend') {
				$page = ':'.$page;
			}
			$page = $plugin.':'.$page;
		}

		return $page;
	}

	protected function backendMenuEvent()
	{
		/**
		 * @event backend_menu
		 * @return array[string]array $return[%plugin_id%]
		 * @return array[string][string]string $return[%plugin_id%]['aux_li'] Single menu items
		 * @return array[string][string]string $return[%plugin_id%]['core_li'] Single menu items
		 */
		return wa()->event('backend_menu');
	}
}