<?php

class mylangBackendTranslateController extends waJsonController
{
    /**
     * input POST
     * [source language|empty]
     * [target language]
     * [provider string]
     * [text string]
     * @return bool|mixed|string|void
     * @throws waException
     */
    public function execute()
    {
        $params = waRequest::post();
        if (empty($params['target'])) {
            return $this->errors = _w('Language is empty.');
        }
        if (empty($params['provider'])) {
            return $this->errors = _w('Empty provider.');
        }
        if (empty($params['text'])) {
            return $this->response = '';
        }
        if (empty($params['source'])) {
            $params['source'] = mylangHelper::getSettings('main_language');
        }
        /* @var $provider mylangAutoTranslate
         */
        $provider = wa('mylang')->getConfig()->getProvider($params);
        if ($provider) {
            $result = $provider->translate($params['text']);
            $result = str_ireplace(
                ['{$ name}', '{$ summary}', '{$ price}'],
                ['{$name}', '{$summary}', '{$price}'],
                $result
            );
            if ($result) {
                return $this->response = $result;
            }
            $this->errors = $provider->getErrors();
        } else {
            $this->errors = _w('No translation plugin.');
        }
        return $this->errors;
    }
}
