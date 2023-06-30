<?php

class sitemapStructureSettingsSaveController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;
		$applications_structure_state_json = waRequest::post('applications_structure_state');
		$applications_sub_structures_state_json = waRequest::post('applications_sub_structures_state');
		$applications_settings_state_json = waRequest::post('applications_settings_state');

		$applications_structure_state = json_decode($applications_structure_state_json, true);
		$applications_sub_structures_state = json_decode($applications_sub_structures_state_json, true);
		$applications_settings_state = json_decode($applications_settings_state_json, true);
		if (!is_array($applications_structure_state) || !is_array($applications_settings_state))
		{
			return;
		}

		$this->saveStructure($applications_structure_state, $applications_sub_structures_state);
		$this->saveSettings($applications_settings_state);

		$this->response['domain_with_personal_settings'] = $this->getDomainWithPersonalSettings();
		$this->response['success'] = true;
	}

	protected function saveStructure($applications_structure_state, $applications_sub_structures_state)
	{
		$storage_factory = new sitemapApplicationStructureStorageFactory();
		foreach ($applications_structure_state as $domain_id => $applications_structure)
		{
			foreach ($applications_structure as $app_id => $application_structure_assoc)
			{
				try
				{
					$storage = $storage_factory->getForApp($app_id);
				}
				catch (sitemapException $e)
				{
					continue;
				}

				$elements_assoc = $application_structure_assoc['elements'];
				if (
					array_key_exists($domain_id, $applications_sub_structures_state)
					&& is_array($applications_sub_structures_state[$domain_id])
					&& array_key_exists($app_id, $applications_sub_structures_state[$domain_id])
					&& is_array($applications_sub_structures_state[$domain_id][$app_id])
				)
				{
					foreach ($applications_sub_structures_state[$domain_id][$app_id] as $substructure_id => $substructure_assoc)
					{
						foreach ($substructure_assoc['elements'] as $element_assoc)
						{
							$elements_assoc[] = $element_assoc;
						}
					}
				}

				$storage->storeElements(
					$domain_id,
					$application_structure_assoc['route_url'],
					$elements_assoc
				);
			}
		}
	}

	private function saveSettings($applications_settings_state)
	{
		$settings_storage_factory = new sitemapApplicationSettingsStorageFactory();

		foreach ($applications_settings_state as $domain_id => $application_settings)
		{
			foreach ($application_settings as $app_id => $settings_assoc)
			{
				try
				{
					$storage = $settings_storage_factory->getForApp($app_id);
				}
				catch (sitemapException $e)
				{
					continue;
				}

				$storage->save($domain_id, sitemapSettingsStorage::DEFAULT_ROUTE_URL, $settings_assoc);
			}
		}
	}

	private function getDomainWithPersonalSettings()
	{
		$model = new sitemapApplicationStructureModel();

		$domains = array();
		foreach ($model->select('DISTINCT domain_id')->fetchAll() as $row)
		{
			$domains[] = intval($row['domain_id']);
		}

		return $domains;
	}
}