<?php

class sitemapRepairActions extends waJsonActions
{
	protected $response = 'Ok';

	public function defaultAction()
	{
		$this->response = "Доступные action'ы:\r\n\tclean - удаление лишних файлов в wa-apps/sitemap\r\n\trestoreCleanSystemConfig - восстановить оригинальный wa-config/SystemConfig.class.php";
	}

	public function cleanAction()
	{
		$cleaner = new sitemapCleaner();

		$cleaner->clean();
	}

	public function restoreCleanSystemConfigAction()
	{
		$original_system_config_content = '
<?php

require_once realpath(dirname(__FILE__).\'/../\').\'/wa-system/autoload/waAutoload.class.php\';
waAutoload::register();

class SystemConfig extends waSystemConfig
{
}
';

		$system_config_path = wa()->getConfigPath() . '/SystemConfig.class.php';

		$write_result = file_put_contents($system_config_path, $original_system_config_content);

		if ($write_result === false) {
			$this->response = 'Error';
		}
	}

	public function run($params = null)
	{
		$action = $params;
		if (!$action)
		{
			$action = 'default';
		}
		$this->action = $action;
		$this->preExecute();
		$this->execute($this->action);
		$this->postExecute();

		if ($this->action == $action)
		{
			if (waRequest::isXMLHttpRequest())
			{
				$this->getResponse()->addHeader('Content-type', 'application/json');
			}
			$this->getResponse()->sendHeaders();
			if (!$this->errors)
			{
				echo '<pre>' . $this->response . '</pre>';
			}
			else
			{
				echo '<pre>' . json_encode(array('status' => 'fail', 'errors' => $this->errors)) . '</pre>';
			}
		}
	}
}