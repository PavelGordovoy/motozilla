<?php

abstract class sitemapJsonController extends waController
{
	const HTTP_STATUS_SUCCESS = 200;
	const HTTP_STATUS_ERROR = 404;
	const HTTP_STATUS_ACCESS_ERROR = 403;

	protected $errors = array();

	/**
	 * @return array
	 */
	public function execute()
	{
		return array();
	}

	public function errors()
	{
		return $this->errors;
	}

	public function run($params = null)
	{
		$response = array(
			'success' => false,
		);

		if ($this->preExecute() === false)
		{
			$success = false;
			$code = self::HTTP_STATUS_ERROR;
		}
		else
		{
			try
			{
				$response = $this->execute();

				$code = self::HTTP_STATUS_SUCCESS;
				$success = true;
			}
			catch (sitemapException $exception)
			{
				//$code = $exception->getCode();
				$code = self::HTTP_STATUS_SUCCESS;

				$response['errors'] = $this->errors();
				$response['error_message'] = $exception->getMessage();

				$success = false;
			}
		}

		$response['success'] = $success;
		$this->display($response, $code);
	}

	public function display($response, $code)
	{
		if (waRequest::isXMLHttpRequest())
		{
			$this->getResponse()->addHeader('Content-Type', 'application/json');
		}

		$this->getResponse()->setStatus($code);
		$this->getResponse()->sendHeaders();

		echo json_encode($response);
	}

	protected function addError($field, $message = '')
	{
		$this->errors[$field] = $message;
	}
}