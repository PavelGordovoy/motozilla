<?php

class sitemapStructureSettingsGetDomainStructuresController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		$domain_id = waRequest::get('domain_id', 0, waRequest::TYPE_INT);

		if ($domain_id < 0)
		{
			return;
		}

		$structures_assoc = array();
		$applications_structure = $this->getApplicationsStructure($domain_id);
		foreach ($applications_structure as $app_id => $application_structure)
		{
			$structures_assoc[$app_id] = $application_structure->getAssoc();
		}


		$substructures_assoc = array();
		$applications_substructures = $this->getApplicationsSubstructures($domain_id);
		foreach ($applications_substructures as $app_id => $application_substructures)
		{
			$substructures_assoc[$app_id] = array();

			foreach ($application_substructures as $substructure_id => $application_substructure)
			{
				$substructures_assoc[$app_id][$substructure_id] = $application_substructure->getAssoc();
			}
		}


		$this->response['structures'] = $structures_assoc;
		$this->response['substructures'] = $substructures_assoc;
		$this->response['success'] = true;
	}

	/**
	 * @param int $domain_id
	 * @return sitemapApplicationStructure[]
	 * @throws waException
	 */
	private function getApplicationsStructure($domain_id)
	{
		$structure_storage_factory = new sitemapApplicationStructureStorageFactory();

		$applications_structure = array();

		foreach (wa()->getApps() as $app_id => $app_config)
		{
			try
			{
				$structure_storage = $structure_storage_factory->getForApp($app_id);
			}
			catch (waException $e)
			{
				continue;
			}

			$application_structure = $structure_storage->get(
				$domain_id,
				sitemapApplicationStructureStorage::DEFAULT_ROUTE_URL
			);
			$applications_structure[$app_id] = $application_structure;
		}

		return $applications_structure;
	}

	/**
	 * @param int $domain_id
	 * @return sitemapApplicationStructure[][]
	 * @throws waException
	 */
	private function getApplicationsSubstructures($domain_id)
	{
		$structure_storage_factory = new sitemapApplicationStructureStorageFactory();

		$applications_substructures = array();

		foreach (wa()->getApps() as $app_id => $app_config)
		{
			try
			{
				$structure_storage = $structure_storage_factory->getForApp($app_id);
			}
			catch (waException $e)
			{
				continue;
			}

			$application_substructures = $structure_storage->getSubstructures(
				$domain_id,
				sitemapApplicationStructureStorage::DEFAULT_ROUTE_URL
			);
			$applications_substructures[$app_id] = $application_substructures;
		}

		return $applications_substructures;
	}
}