<?php

/** @noinspection PhpUndefinedMethodInspection */

class mylangContactsAction extends waViewAction
{
    public function execute()
    {
        if (!waRequest::isXMLHttpRequest()) {
            $this->setLayout(new mylangBackendLayout());
        }
        $all_fields = waContactFields::getAll('all', true);
        $fields = [];
        foreach ($all_fields as $value) {
            if (get_class($value) === 'waContactHiddenField') {
                continue;
            }
            $fields[$value->getId()] = [
                'name'=>$value->getName(),
                'localized_names' => $value->getParameter('localized_names')
            ];

            if (method_exists(get_class($value), 'getFields' )) {
                $subfields = $value->getFields();
                foreach ($subfields as $sid=>$sf) {
                    if (get_class($sf) === 'waContactHiddenField') {
                        continue;
                    }
                    $fields[$value->getId()]['subfields'][$sf->getId()] = [
                        'name'=>$sf->getName(),
                        'localized_names' => $sf->getParameter('localized_names')
                    ];
                }
            }
        }

        wa('mylang', 1);
        $this->view->assign('fields', $fields);
        $this->view->assign('locales', waLocale::getAll('name'));
    }
}
